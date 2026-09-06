<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class NotificationCenterController extends Controller
{
    public function index(Request $request)
    {
        $this->syncReminders($request);
        $notifications = $request->user()->notifications()->latest()->paginate(30);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        $item->markAsRead();

        return back()->with('status', 'Notification marked as read.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'All notifications marked as read.');
    }

    public function refresh(Request $request): RedirectResponse
    {
        $created = $this->syncReminders($request);

        return back()->with('status', $created ? $created.' new reminder'.($created === 1 ? ' was' : 's were').' added.' : 'Your reminders are already up to date.');
    }

    private function syncReminders(Request $request): int
    {
        $user = $request->user();

        // A missing preference means this is an existing account created before
        // Step 14, so keep the original enabled behaviour until the user chooses.
        if ($user->preferences?->in_app_reminders === false) {
            return 0;
        }

        $today = today();
        $until = today()->addDays(7);
        $candidates = collect();

        if (Schema::hasColumn('job_applications', 'follow_up_at')) {
            $user->jobApplications()->whereNotNull('follow_up_at')->whereBetween('follow_up_at', [$today, $until])->get()
                ->each(fn ($job) => $candidates->push([
                    'key' => 'job-follow-up-'.$job->id.'-'.$job->follow_up_at->format('Y-m-d'),
                    'title' => 'Follow up with '.$job->company,
                    'body' => $job->role.' follow-up is due '.$job->follow_up_at->format('M j').'.',
                    'url' => route('jobs'), 'due_on' => $job->follow_up_at->toDateString(), 'kind' => 'job_follow_up',
                ]));
        }

        if (Schema::hasColumn('interview_sessions', 'reminder_at')) {
            $user->interviewSessions()->whereNotNull('reminder_at')->whereBetween('reminder_at', [$today->copy()->startOfDay(), $until->copy()->endOfDay()])->get()
                ->each(fn ($interview) => $candidates->push([
                    'key' => 'interview-'.$interview->id.'-'.$interview->reminder_at->format('Y-m-d'),
                    'title' => 'Interview practice reminder',
                    'body' => ($interview->title ?: $interview->target_role ?: 'Practice session').' is scheduled for '.$interview->reminder_at->format('M j, g:i A').'.',
                    'url' => route('interviews'), 'due_on' => $interview->reminder_at->toDateString(), 'kind' => 'interview',
                ]));
        }

        $user->careerGoals()->whereNotNull('target_date')->whereBetween('target_date', [$today, $until])->get()
            ->each(fn ($goal) => $candidates->push([
                'key' => 'career-goal-'.$goal->id.'-'.$goal->target_date->format('Y-m-d'),
                'title' => 'Career goal deadline is close',
                'body' => $goal->title.' has a target date of '.$goal->target_date->format('M j').'.',
                'url' => route('insights'), 'due_on' => $goal->target_date->toDateString(), 'kind' => 'career_goal',
            ]));

        $known = $user->notifications()->get()->map(fn ($notification) => data_get($notification->data, 'key'))->filter()->flip();
        $created = 0;
        foreach ($candidates->unique('key') as $candidate) {
            if (! $known->has($candidate['key'])) {
                $user->notifications()->create([
                    'id' => (string) Str::uuid(), 'type' => 'career_reminder', 'data' => $candidate,
                ]);
                $created++;
            }
        }

        return $created;
    }
}
