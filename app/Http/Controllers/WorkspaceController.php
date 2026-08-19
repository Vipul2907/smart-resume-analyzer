<?php

namespace App\Http\Controllers;

use App\Models\CareerGoal;
use App\Models\CareerProfile;
use App\Models\InterviewSession;
use App\Models\JobApplication;
use App\Models\JobAttachment;
use App\Models\JobContact;
use App\Models\PortfolioProject;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
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
        }

        if ($screen === 'skills') {
            $data['skills'] = $user->skills()->latest()->get();
        }

        if ($screen === 'insights') {
            $data['goals'] = $user->careerGoals()->latest()->get();
            $data['profile'] = $user->careerProfile;
        }

        if ($screen === 'portfolio') {
            $data['projects'] = $user->portfolioProjects()->latest()->get();
        }

        if (in_array($screen, ['dashboard', 'analytics'], true)) {
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

    public function storeJob(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:saved,applied,interviewing,offer,rejected,withdrawn'],
            'location' => ['nullable', 'string', 'max:255'],
            'work_mode' => ['nullable', 'string', 'max:50'],
            'job_url' => ['nullable', 'url', 'max:2048'],
            'applied_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'follow_up_at' => ['nullable', 'date'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:3'],
        ]);

        $this->create(JobApplication::class, 'job_applications', $data + [
            'user_id' => $request->user()->id,
            'work_type' => $data['work_mode'] ?? null,
            'application_date' => $data['applied_at'] ?? null,
        ]);

        return back()->with('status', 'Job application saved.');
    }

    public function updateJob(Request $request, JobApplication $job): RedirectResponse
    {
        $this->owns($request, $job);
        $data = $request->validate([
            'company' => ['required', 'string', 'max:255'], 'role' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:saved,applied,interviewing,offer,rejected,withdrawn'],
            'location' => ['nullable', 'string', 'max:255'], 'work_mode' => ['nullable', 'string', 'max:50'],
            'job_url' => ['nullable', 'url', 'max:2048'], 'applied_at' => ['nullable', 'date'],
            'follow_up_at' => ['nullable', 'date'], 'priority' => ['nullable', 'integer', 'min:0', 'max:3'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
        $this->updateAvailable($job, 'job_applications', $data + ['work_type' => $data['work_mode'] ?? null, 'application_date' => $data['applied_at'] ?? null]);

        return back()->with('status', 'Application status updated.');
    }

    public function storeJobContact(Request $request, JobApplication $job): RedirectResponse
    {
        $this->owns($request, $job);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'role' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'], 'linkedin_url' => ['nullable', 'url', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $job->contacts()->create($data);

        return back()->with('status', 'Recruiter contact saved.');
    }

    public function destroyJobContact(Request $request, JobApplication $job, JobContact $contact): RedirectResponse
    {
        $this->owns($request, $job);
        abort_unless($contact->job_application_id === $job->id, 404);
        $contact->delete();

        return back()->with('status', 'Recruiter contact removed.');
    }

    public function storeJobAttachment(Request $request, JobApplication $job): RedirectResponse
    {
        $this->owns($request, $job);
        $request->validate(['attachment' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,txt,png,jpg,jpeg']]);
        $file = $request->file('attachment');
        $path = $file->store('job-attachments/'.$request->user()->id.'/'.$job->id, 'local');
        $job->attachments()->create([
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'original_filename' => $file->getClientOriginalName(), 'file_path' => $path, 'file_disk' => 'local',
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream', 'file_size' => $file->getSize(),
        ]);

        return back()->with('status', 'Private attachment uploaded.');
    }

    public function downloadJobAttachment(Request $request, JobApplication $job, JobAttachment $attachment)
    {
        $this->owns($request, $job);
        abort_unless($attachment->job_application_id === $job->id && Storage::disk($attachment->file_disk)->exists($attachment->file_path), 404);

        return Storage::disk($attachment->file_disk)->download($attachment->file_path, $attachment->original_filename);
    }

    public function destroyJobAttachment(Request $request, JobApplication $job, JobAttachment $attachment): RedirectResponse
    {
        $this->owns($request, $job);
        abort_unless($attachment->job_application_id === $job->id, 404);
        Storage::disk($attachment->file_disk)->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('status', 'Attachment removed.');
    }

    public function destroyJob(Request $request, JobApplication $job): RedirectResponse
    {
        $this->owns($request, $job);
        $job->delete();

        return back()->with('status', 'Job application removed.');
    }

    public function storeInterview(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'target_role' => ['nullable', 'string', 'max:255'],
            'session_type' => ['required', 'in:general,technical,behavioral'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:180'],
            'job_application_id' => ['nullable', 'integer'],
        ]);
        if ($data['job_application_id'] ?? null) {
            abort_unless($request->user()->jobApplications()->whereKey($data['job_application_id'])->exists(), 404);
        }

        $this->create(InterviewSession::class, 'interview_sessions', $data + [
            'user_id' => $request->user()->id,
            'status' => 'planned',
            'type' => $data['session_type'],
        ]);

        return back()->with('status', 'Interview practice session created.');
    }

    public function completeInterview(Request $request, InterviewSession $interview): RedirectResponse
    {
        $this->owns($request, $interview);
        $data = $request->validate(['score' => ['nullable', 'integer', 'min:0', 'max:100']]);
        $this->updateAvailable($interview, 'interview_sessions', [
            'status' => 'completed', 'completed_at' => now(), 'score' => $data['score'], 'overall_score' => $data['score'],
        ]);

        return back()->with('status', 'Interview session marked complete.');
    }

    public function destroyInterview(Request $request, InterviewSession $interview): RedirectResponse
    {
        $this->owns($request, $interview);
        $interview->delete();

        return back()->with('status', 'Interview session removed.');
    }

    public function storeSkill(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'category' => ['nullable', 'string', 'max:255'],
            'proficiency' => ['nullable', 'integer', 'min:0', 'max:100'], 'years_experience' => ['nullable', 'numeric', 'min:0', 'max:99'],
            'is_priority' => ['nullable', 'boolean'], 'evidence' => ['nullable', 'string', 'max:1000'],
        ]);
        $this->create(Skill::class, 'skills', $data + ['user_id' => $request->user()->id, 'is_learning' => false]);

        return back()->with('status', 'Skill saved.');
    }

    public function destroySkill(Request $request, Skill $skill): RedirectResponse
    {
        $this->owns($request, $skill);
        $skill->delete();

        return back()->with('status', 'Skill removed.');
    }

    public function storeGoal(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'], 'target_role' => ['nullable', 'string', 'max:255'],
            'target_date' => ['nullable', 'date'], 'progress' => ['nullable', 'integer', 'min:0', 'max:100'], 'motivation' => ['nullable', 'string', 'max:2000'],
        ]);
        $request->user()->careerGoals()->create($data + ['status' => 'active', 'progress' => $data['progress'] ?? 0]);

        return back()->with('status', 'Career goal saved.');
    }

    public function updateGoal(Request $request, CareerGoal $goal): RedirectResponse
    {
        $this->owns($request, $goal);
        $data = $request->validate(['progress' => ['required', 'integer', 'min:0', 'max:100']]);
        $goal->update($data + ['status' => $data['progress'] === 100 ? 'completed' : 'active']);

        return back()->with('status', 'Goal progress updated.');
    }

    public function destroyGoal(Request $request, CareerGoal $goal): RedirectResponse
    {
        $this->owns($request, $goal);
        $goal->delete();

        return back()->with('status', 'Career goal removed.');
    }

    public function storeProject(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'], 'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'], 'role' => ['nullable', 'string', 'max:255'],
            'project_url' => ['nullable', 'url', 'max:2048'], 'repository_url' => ['nullable', 'url', 'max:2048'],
        ]);
        $this->create(PortfolioProject::class, 'portfolio_projects', $data + [
            'user_id' => $request->user()->id, 'status' => 'completed', 'slug' => Str::slug($data['title']).'-'.Str::lower(Str::random(5)),
            'short_description' => $data['tagline'] ?? null, 'github_url' => $data['repository_url'] ?? null, 'visibility' => 'private',
        ]);

        return back()->with('status', 'Portfolio project saved.');
    }

    public function destroyProject(Request $request, PortfolioProject $project): RedirectResponse
    {
        $this->owns($request, $project);
        $project->delete();

        return back()->with('status', 'Portfolio project removed.');
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

    private function create(string $model, string $table, array $data): void
    {
        $columns = array_flip(Schema::getColumnListing($table));
        $model::create(array_intersect_key($data, $columns));
    }

    private function updateAvailable(object $model, string $table, array $data): void
    {
        $columns = array_flip(Schema::getColumnListing($table));
        $model->update(array_intersect_key($data, $columns));
    }

    private function owns(Request $request, object $model): void
    {
        abort_unless($model->user_id === $request->user()->id, 404);
    }

    private function jobStatuses(): array
    {
        return ['saved', 'applied', 'interviewing', 'offer', 'rejected', 'withdrawn'];
    }
}
