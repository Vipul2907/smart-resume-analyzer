<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithWorkspaceRecords;
use App\Models\CareerGoal;
use App\Models\CareerProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
    use InteractsWithWorkspaceRecords;

    public function show(Request $request, string $screen): View
    {
        $user = $request->user();
        $data = ['screen' => $screen];

        if ($screen === 'jobs') {
            $filter = $request->string('status')->toString();
            $query = $user->jobApplications()->with(['contacts', 'attachments'])->latest();
            if (in_array($filter, $this->jobStatuses(), true)) {
                $query->where('status', $filter);
            }
            if ($request->filled('search')) {
                $term = '%'.$request->string('search')->toString().'%';
                $query->where(fn ($builder) => $builder->where('company', 'like', $term)->orWhere('role', 'like', $term));
            }
            $data['jobs'] = $query->get();
            $data['jobCounts'] = $user->jobApplications()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
            $data['activeFilter'] = $filter;

            return view('workspace.jobs', $data);
        }

        if ($screen === 'interviews') {
            $data['interviews'] = $user->interviewSessions()->latest()->get();
            $data['jobs'] = $user->jobApplications()->latest()->get(['id', 'company', 'role']);

            return view('workspace.interviews', $data);
        }

        if ($screen === 'skills') {
            $data['skills'] = $user->skills()->with('milestones')->latest()->get();
            $data['recommendedSkills'] = $user->aiAnalyses()
                ->where('analysis_type', 'job_match')->where('status', 'completed')->latest()->limit(5)->get()
                ->flatMap(fn ($analysis) => is_array($analysis->result) ? ($analysis->result['missing_skills'] ?? []) : [])
                ->filter(fn ($skill) => is_string($skill) && trim($skill) !== '')
                ->map(fn (string $skill) => trim($skill))->unique()->values();

            return view('workspace.skills', $data);
        }

        if ($screen === 'insights') {
            $data['goals'] = $user->careerGoals()->latest()->get()->map(function (CareerGoal $goal): CareerGoal {
                $milestones = collect($goal->milestones ?? []);
                $goal->setAttribute('milestone_summary', [
                    'total' => $milestones->count(),
                    'completed' => $milestones->where('status', 'completed')->count(),
                ]);

                return $goal;
            });
            $data['profile'] = $user->careerProfile;

            return view('workspace.insights', $data);
        }

        if ($screen === 'portfolio') {
            $data['projects'] = $user->portfolioProjects()->latest()->get();
            $data['profile'] = $user->careerProfile;
            $data['primaryResume'] = $user->resumes()->where('is_primary', true)->first() ?: $user->resumes()->latest()->first();

            return view('workspace.portfolio', $data);
        }

        if ($screen === 'dashboard') {
            $data['recentJobs'] = $user->jobApplications()->latest()->limit(4)->get();
            $data['upcomingFollowUps'] = $user->jobApplications()->whereNotNull('follow_up_at')->whereDate('follow_up_at', '>=', today())->orderBy('follow_up_at')->limit(4)->get();
            $data['recentInterviews'] = $user->interviewSessions()->latest()->limit(3)->get();
            $data['primaryResume'] = $user->resumes()->where('is_primary', true)->first() ?: $user->resumes()->latest()->first();
            $data['dashboardStats'] = [
                'applications' => $user->jobApplications()->count(),
                'activeApplications' => $user->jobApplications()->whereIn('status', ['applied', 'interviewing', 'offer'])->count(),
                'completedInterviews' => $user->interviewSessions()->where('status', 'completed')->count(),
                'skills' => $user->skills()->count(),
            ];
            $data['prioritySkills'] = $user->skills()->orderByDesc('is_priority')->orderByDesc('proficiency')->limit(4)->get();
            $data['activeGoal'] = $user->careerGoals()->where('progress', '<', 100)->latest()->first();
            $data['recentAnalyses'] = $user->aiAnalyses()->where('status', 'completed')->latest()->limit(3)->get();
            $data['resumeReadiness'] = ! $data['primaryResume'] ? 0 : ($data['primaryResume']->last_analyzed_at ? 100 : ($data['primaryResume']->parse_status === 'parsed' ? 65 : 30));

            return view('workspace.dashboard', $data);
        }

        if ($screen === 'analytics') {
            $jobs = $user->jobApplications()->get();
            $interviews = $user->interviewSessions()->get();
            $data['metrics'] = [
                'applications' => $jobs->count(),
                'active' => $jobs->whereIn('status', ['applied', 'interviewing', 'offer'])->count(),
                'interviews' => $interviews->count(),
                'completed_interviews' => $interviews->where('status', 'completed')->count(),
                'skills' => $user->skills()->count(),
                'projects' => $user->portfolioProjects()->count(),
            ];
            $data['jobStatuses'] = $jobs->groupBy('status')->map->count();
        }

        if (in_array($screen, ['profile', 'settings'], true)) {
            $data['profile'] = $user->careerProfile;
        }

        return view('workspace.index', $data);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'headline' => ['nullable', 'string', 'max:255'], 'location' => ['nullable', 'string', 'max:255'],
            'about' => ['nullable', 'string', 'max:3000'], 'linkedin_url' => ['nullable', 'url', 'max:2048'], 'website_url' => ['nullable', 'url', 'max:2048'],
            'available_for_work' => ['nullable', 'boolean'],
        ]);
        $request->user()->update(['name' => $data['name']]);
        CareerProfile::updateOrCreate(
            ['user_id' => $request->user()->id],
            collect($data)->except('name')->all() + ['available_for_work' => (bool) ($data['available_for_work'] ?? false)]
        );

        return back()->with('status', 'Profile saved.');
    }

    private function jobStatuses(): array
    {
        return ['saved', 'applied', 'interviewing', 'offer', 'rejected', 'withdrawn', 'closed'];
    }
}
