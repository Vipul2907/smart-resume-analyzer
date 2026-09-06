<?php

namespace App\Http\Controllers;

use App\Models\AdminAnnouncement;
use App\Models\SupportRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);
        $search = trim($request->string('q')->toString());
        $users = User::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($builder) => $builder->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();
        $tickets = SupportRequest::query()->with('user')->latest()->limit(12)->get();

        return view('admin.index', [
            'users' => $users,
            'tickets' => $tickets,
            'announcements' => AdminAnnouncement::query()->with('author')->latest()->limit(10)->get(),
            'metrics' => $this->metrics(),
            'aiUsage' => $this->aiUsage(),
            'search' => $search,
        ]);
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $data = $request->validate(['is_admin' => ['required', 'boolean']]);

        if ($user->is($request->user()) && ! (bool) $data['is_admin']) {
            return back()->with('error', 'You cannot remove your own admin access. Ask another administrator to do that.');
        }

        $user->update(['is_admin' => (bool) $data['is_admin']]);

        return back()->with('status', $user->is_admin ? 'Administrator access granted.' : 'Administrator access removed.');
    }

    public function updateTicket(Request $request, SupportRequest $supportRequest): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $data = $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
            'admin_response' => ['nullable', 'string', 'max:5000'],
        ]);
        $supportRequest->update($data);

        return back()->with('status', 'Support request updated.');
    }

    public function storeAnnouncement(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:3000'],
        ]);

        $announcement = AdminAnnouncement::create($data + [
            'user_id' => $request->user()->id,
            'published_at' => now(),
        ]);

        if (Schema::hasTable('notifications')) {
            User::query()->whereNotNull('email_verified_at')->select('id')->chunkById(100, function ($users) use ($announcement): void {
                foreach ($users as $recipient) {
                    $recipient->notifications()->create([
                        'id' => (string) Str::uuid(),
                        'type' => 'platform_announcement',
                        'data' => [
                            'key' => 'announcement-'.$announcement->id,
                            'title' => $announcement->title,
                            'body' => $announcement->body,
                            'url' => route('help'),
                            'kind' => 'announcement',
                        ],
                    ]);
                }
            });
        }

        return back()->with('status', 'Announcement published to verified SmartCV users.');
    }

    public function destroyAnnouncement(Request $request, AdminAnnouncement $announcement): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $announcement->delete();

        return back()->with('status', 'Announcement removed from the admin history. Existing user notifications are kept as a record.');
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless((bool) $request->user()->is_admin, 403, 'This area is only available to SmartCV administrators.');
    }

    /** @return array<string, int> */
    private function metrics(): array
    {
        return [
            'users' => User::count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count(),
            'resumes' => Schema::hasTable('resumes') ? \App\Models\Resume::count() : 0,
            'applications' => Schema::hasTable('job_applications') ? \App\Models\JobApplication::count() : 0,
            'open_tickets' => SupportRequest::whereIn('status', ['open', 'in_progress'])->count(),
            'announcements' => AdminAnnouncement::count(),
        ];
    }

    /** @return \Illuminate\Support\Collection<int, array<string, mixed>> */
    private function aiUsage(): \Illuminate\Support\Collection
    {
        if (! Schema::hasTable('ai_analyses')) {
            return collect();
        }

        return \App\Models\AiAnalysis::query()
            ->selectRaw('analysis_type, status, count(*) as total')
            ->groupBy('analysis_type', 'status')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($item) => ['type' => str_replace('_', ' ', (string) $item->analysis_type), 'status' => $item->status, 'total' => (int) $item->total]);
    }
}
