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
use App\Models\SkillMilestone;
use App\Services\GroqAiService;
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

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($attachment->file_disk);

        return $disk->download($attachment->file_path, $attachment->original_filename);
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
            'session_type' => ['required', 'in:general,technical,behavioral,hr,leadership,case_study'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:180'],
            'job_application_id' => ['nullable', 'integer'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'reminder_at' => ['nullable', 'date'],
        ]);
        if ($data['job_application_id'] ?? null) {
            abort_unless($request->user()->jobApplications()->whereKey($data['job_application_id'])->exists(), 404);
        }

        $questions = $this->interviewQuestions($data['session_type'], $data['target_role'] ?? 'your target role');
        $this->create(InterviewSession::class, 'interview_sessions', $data + [
            'user_id' => $request->user()->id,
            'status' => 'in_progress',
            'type' => $data['session_type'],
            'questions' => $questions,
            'questions_count' => count($questions),
            'completed_questions' => 0,
        ]);

        return back()->with('status', 'Interview practice is ready. Answer the questions, save your progress, then mark it complete.');
    }

    public function saveInterviewResponses(Request $request, InterviewSession $interview): RedirectResponse
    {
        $this->owns($request, $interview);
        $data = $request->validate(['answers' => ['nullable', 'array'], 'answers.*' => ['nullable', 'string', 'max:5000']]);
        $answers = collect($data['answers'] ?? [])->map(fn ($answer) => trim((string) $answer))->all();
        $this->updateAvailable($interview, 'interview_sessions', [
            'responses' => $answers,
            'completed_questions' => collect($answers)->filter()->count(),
            'status' => 'in_progress',
            'started_at' => $interview->started_at ?: now(),
        ]);

        return back()->with('status', 'Your interview answers have been saved.');
    }

    public function completeInterview(Request $request, InterviewSession $interview, GroqAiService $groq): RedirectResponse
    {
        $this->owns($request, $interview);
        $answers = collect($interview->responses ?? [])->map(fn ($answer) => trim((string) $answer))->filter();
        if ($answers->isEmpty()) {
            return back()->withErrors(['answers' => 'Save at least one written answer before calculating your readiness score.']);
        }
        if ($answers->contains(fn (string $answer) => ! $this->isMeaningfulInterviewAnswer($answer))) {
            return back()->withErrors(['answers' => 'Your answers need real sentences with specific details. Random letters or very short text cannot be scored.']);
        }

        try {
            $feedback = $groq->evaluateInterviewResponses($interview);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['answers' => 'SmartCV could not analyse your answers right now. Check your Groq setup and try again.']);
        }
        $this->updateAvailable($interview, 'interview_sessions', [
            'status' => 'completed', 'completed_at' => now(), 'score' => $feedback['score'], 'overall_score' => $feedback['score'], 'feedback' => $feedback,
        ]);

        return back()->with('status', 'Interview session completed. Your readiness score was calculated from your saved answers.');
    }

    public function storeInterviewRecording(Request $request, InterviewSession $interview): RedirectResponse
    {
        $this->owns($request, $interview);
        $request->validate(['recording' => ['required', 'file', 'max:102400', 'mimes:mp3,m4a,wav,webm,mp4,mov']]);
        $file = $request->file('recording');
        if ($interview->recording_path) {
            Storage::disk($interview->recording_disk ?: 'local')->delete($interview->recording_path);
        }
        $this->updateAvailable($interview, 'interview_sessions', [
            'recording_path' => $file->store('interview-recordings/'.$request->user()->id.'/'.$interview->id, 'local'),
            'recording_disk' => 'local',
            'recording_original_filename' => $file->getClientOriginalName(),
            'recording_mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'recording_size' => $file->getSize(),
        ]);

        return back()->with('status', 'Practice recording saved privately.');
    }

    public function downloadInterviewRecording(Request $request, InterviewSession $interview)
    {
        $this->owns($request, $interview);
        $diskName = $interview->recording_disk ?: 'local';
        abort_unless($interview->recording_path && Storage::disk($diskName)->exists($interview->recording_path), 404);

        return Storage::disk($diskName)->download($interview->recording_path, $interview->recording_original_filename ?: 'interview-recording');
    }

    public function playInterviewRecording(Request $request, InterviewSession $interview)
    {
        $this->owns($request, $interview);
        $diskName = $interview->recording_disk ?: 'local';
        abort_unless($interview->recording_path && Storage::disk($diskName)->exists($interview->recording_path), 404);

        return Storage::disk($diskName)->response(
            $interview->recording_path,
            $interview->recording_original_filename,
            ['Content-Type' => $interview->recording_mime_type ?: 'application/octet-stream', 'Content-Disposition' => 'inline']
        );
    }

    public function destroyInterview(Request $request, InterviewSession $interview): RedirectResponse
    {
        $this->owns($request, $interview);
        if ($interview->recording_path) {
            Storage::disk($interview->recording_disk ?: 'local')->delete($interview->recording_path);
        }
        $interview->delete();

        return back()->with('status', 'Interview session removed.');
    }

    public function storeSkill(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'category' => ['nullable', 'string', 'max:255'],
            'proficiency' => ['nullable', 'integer', 'min:0', 'max:100'], 'years_experience' => ['nullable', 'numeric', 'min:0', 'max:99'],
            'is_priority' => ['nullable', 'boolean'], 'evidence' => ['nullable', 'string', 'max:1000'],
            'certificate' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp'],
        ]);
        $certificate = [];
        if ($request->hasFile('certificate')) {
            $file = $request->file('certificate');
            $certificate = [
                'certificate_original_filename' => $file->getClientOriginalName(),
                'certificate_path' => $file->store('skill-certificates/'.$request->user()->id, 'local'),
                'certificate_disk' => 'local',
                'certificate_mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'certificate_size' => $file->getSize(),
            ];
        }
        unset($data['certificate']);
        $this->create(Skill::class, 'skills', $data + $certificate + ['user_id' => $request->user()->id, 'is_learning' => false]);

        return back()->with('status', 'Skill saved.');
    }

    public function destroySkill(Request $request, Skill $skill): RedirectResponse
    {
        $this->owns($request, $skill);
        if ($skill->certificate_path) {
            Storage::disk($skill->certificate_disk ?: 'local')->delete($skill->certificate_path);
        }
        $skill->delete();

        return back()->with('status', 'Skill removed.');
    }

    public function updateSkill(Request $request, Skill $skill): RedirectResponse
    {
        $this->owns($request, $skill);
        $data = $request->validate([
            'proficiency' => ['required', 'integer', 'min:0', 'max:100'],
            'target_proficiency' => ['nullable', 'integer', 'min:0', 'max:100'],
            'is_priority' => ['nullable', 'boolean'],
            'evidence' => ['nullable', 'string', 'max:1000'],
        ]);
        $this->updateAvailable($skill, 'skills', $data + ['is_priority' => (bool) ($data['is_priority'] ?? false)]);

        return back()->with('status', 'Skill progress updated.');
    }

    public function storeSkillMilestone(Request $request, Skill $skill): RedirectResponse
    {
        $this->owns($request, $skill);
        $data = $request->validate(['title' => ['required', 'string', 'max:255'], 'target_date' => ['nullable', 'date']]);
        $skill->milestones()->create($data);

        return back()->with('status', 'Learning milestone saved.');
    }

    public function updateSkillMilestone(Request $request, Skill $skill, SkillMilestone $milestone): RedirectResponse
    {
        $this->owns($request, $skill);
        abort_unless($milestone->skill_id === $skill->id, 404);
        $data = $request->validate(['status' => ['required', 'in:planned,in_progress,completed']]);
        $milestone->update($data + ['completed_at' => $data['status'] === 'completed' ? now() : null]);

        return back()->with('status', 'Learning milestone updated.');
    }

    public function destroySkillMilestone(Request $request, Skill $skill, SkillMilestone $milestone): RedirectResponse
    {
        $this->owns($request, $skill);
        abort_unless($milestone->skill_id === $skill->id, 404);
        $milestone->delete();

        return back()->with('status', 'Learning milestone removed.');
    }

    public function downloadSkillCertificate(Request $request, Skill $skill)
    {
        $this->owns($request, $skill);
        $diskName = $skill->certificate_disk ?: 'local';
        abort_unless($skill->certificate_path && Storage::disk($diskName)->exists($skill->certificate_path), 404);

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($diskName);

        return $disk->download($skill->certificate_path, $skill->certificate_original_filename ?: 'certificate');
    }

    public function storeGoal(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'], 'target_role' => ['nullable', 'string', 'max:255'],
            'target_industry' => ['nullable', 'string', 'max:255'], 'target_salary' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'target_date' => ['nullable', 'date'], 'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'motivation' => ['nullable', 'string', 'max:2000'], 'weekly_action' => ['nullable', 'string', 'max:500'],
        ]);
        $this->create(CareerGoal::class, 'career_goals', $data + [
            'user_id' => $request->user()->id,
            'status' => 'active',
            'progress' => $data['progress'] ?? 0,
            'milestones' => [],
        ]);

        return back()->with('status', 'Career goal saved.');
    }

    public function updateGoal(Request $request, CareerGoal $goal): RedirectResponse
    {
        $this->owns($request, $goal);
        $data = $request->validate([
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
            'weekly_action' => ['nullable', 'string', 'max:500'],
        ]);
        $this->updateAvailable($goal, 'career_goals', $data + ['status' => $data['progress'] === 100 ? 'completed' : 'active']);

        return back()->with('status', 'Goal progress updated.');
    }

    public function storeGoalMilestone(Request $request, CareerGoal $goal): RedirectResponse
    {
        $this->owns($request, $goal);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'target_date' => ['nullable', 'date'],
        ]);
        $milestones = is_array($goal->milestones) ? $goal->milestones : [];
        $milestones[] = [
            'id' => (string) Str::uuid(),
            'title' => $data['title'],
            'target_date' => $data['target_date'] ?? null,
            'status' => 'planned',
            'completed_at' => null,
        ];
        $this->updateAvailable($goal, 'career_goals', ['milestones' => $milestones]);

        return back()->with('status', 'Career milestone added.');
    }

    public function updateGoalMilestone(Request $request, CareerGoal $goal, string $milestone): RedirectResponse
    {
        $this->owns($request, $goal);
        $data = $request->validate(['status' => ['required', 'in:planned,in_progress,completed']]);
        $milestones = is_array($goal->milestones) ? $goal->milestones : [];
        $index = collect($milestones)->search(fn ($item) => is_array($item) && ($item['id'] ?? null) === $milestone);
        abort_if($index === false, 404);
        $milestones[$index]['status'] = $data['status'];
        $milestones[$index]['completed_at'] = $data['status'] === 'completed' ? now()->toDateTimeString() : null;
        $this->updateAvailable($goal, 'career_goals', ['milestones' => $milestones]);

        return back()->with('status', 'Career milestone updated.');
    }

    public function destroyGoalMilestone(Request $request, CareerGoal $goal, string $milestone): RedirectResponse
    {
        $this->owns($request, $goal);
        $milestones = collect(is_array($goal->milestones) ? $goal->milestones : []);
        abort_unless($milestones->contains(fn ($item) => is_array($item) && ($item['id'] ?? null) === $milestone), 404);
        $this->updateAvailable($goal, 'career_goals', [
            'milestones' => $milestones->reject(fn ($item) => is_array($item) && ($item['id'] ?? null) === $milestone)->values()->all(),
        ]);

        return back()->with('status', 'Career milestone removed.');
    }

    public function generateCareerAdvice(Request $request, CareerGoal $goal, GroqAiService $groq): RedirectResponse
    {
        $this->owns($request, $goal);
        $user = $request->user();
        $context = [
            'saved_skills' => $user->skills()->latest()->limit(20)->get(['name', 'category', 'proficiency', 'years_experience'])->map->only(['name', 'category', 'proficiency', 'years_experience'])->all(),
            'applications' => $user->jobApplications()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status')->all(),
            'portfolio_project_count' => $user->portfolioProjects()->count(),
            'resume_count' => $user->resumes()->count(),
            'profile_headline' => $user->careerProfile?->headline,
            'completed_milestones' => collect($goal->milestones ?? [])->where('status', 'completed')->count(),
        ];

        try {
            $advice = $groq->createCareerAdvice($goal, $context);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['career_advice' => 'SmartCV could not create career advice right now. Check your Groq setup and try again.']);
        }

        $this->updateAvailable($goal, 'career_goals', [
            'career_advice' => $advice,
            'career_advice_generated_at' => now(),
        ]);

        return back()->with('status', 'Fresh AI career advice is ready for this goal.');
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
            'skills' => ['nullable', 'string', 'max:1000'], 'outcome' => ['nullable', 'string', 'max:2000'],
            'case_study' => ['nullable', 'string', 'max:10000'], 'visibility' => ['required', 'in:private,public'],
            'is_featured' => ['nullable', 'boolean'], 'image' => ['nullable', 'image', 'max:5120'],
        ]);
        $image = $this->portfolioImage($request);
        $this->create(PortfolioProject::class, 'portfolio_projects', array_merge($data, $image, [
            'user_id' => $request->user()->id, 'status' => 'completed', 'slug' => Str::slug($data['title']).'-'.Str::lower(Str::random(5)),
            'short_description' => $data['tagline'] ?? null, 'github_url' => $data['repository_url'] ?? null,
            'skills' => $this->projectSkills($data['skills'] ?? null), 'is_featured' => (bool) ($data['is_featured'] ?? false),
        ]));

        return back()->with('status', 'Portfolio project saved. It stays private unless you chose public visibility.');
    }

    public function updateProject(Request $request, PortfolioProject $project): RedirectResponse
    {
        $this->owns($request, $project);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'], 'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'], 'role' => ['nullable', 'string', 'max:255'],
            'project_url' => ['nullable', 'url', 'max:2048'], 'repository_url' => ['nullable', 'url', 'max:2048'],
            'skills' => ['nullable', 'string', 'max:1000'], 'outcome' => ['nullable', 'string', 'max:2000'],
            'case_study' => ['nullable', 'string', 'max:10000'], 'visibility' => ['required', 'in:private,public'],
            'is_featured' => ['nullable', 'boolean'], 'image' => ['nullable', 'image', 'max:5120'],
        ]);
        $image = $this->portfolioImage($request, $project);
        $this->updateAvailable($project, 'portfolio_projects', array_merge($data, $image, [
            'short_description' => $data['tagline'] ?? null, 'github_url' => $data['repository_url'] ?? null,
            'skills' => $this->projectSkills($data['skills'] ?? null), 'is_featured' => (bool) ($data['is_featured'] ?? false),
        ]));

        return back()->with('status', 'Portfolio project updated.');
    }

    public function updatePortfolioSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'public_slug' => ['required', 'string', 'min:3', 'max:80', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:career_profiles,public_slug,'.optional($request->user()->careerProfile)->id],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'portfolio_is_public' => ['nullable', 'boolean'], 'show_contact_email' => ['nullable', 'boolean'], 'show_resume' => ['nullable', 'boolean'],
        ]);
        $profile = CareerProfile::updateOrCreate(
            ['user_id' => $request->user()->id],
            $data + [
                'portfolio_is_public' => (bool) ($data['portfolio_is_public'] ?? false),
                'show_contact_email' => (bool) ($data['show_contact_email'] ?? false),
                'show_resume' => (bool) ($data['show_resume'] ?? false),
            ]
        );

        return back()->with('status', $profile->portfolio_is_public ? 'Your public portfolio is live.' : 'Your public portfolio is private.');
    }

    public function destroyProject(Request $request, PortfolioProject $project): RedirectResponse
    {
        $this->owns($request, $project);
        if ($project->image_path) {
            Storage::disk($project->image_disk ?: 'local')->delete($project->image_path);
        }
        $project->delete();

        return back()->with('status', 'Portfolio project removed.');
    }

    public function showProjectImage(Request $request, PortfolioProject $project)
    {
        $this->owns($request, $project);
        $diskName = $project->image_disk ?: 'local';
        abort_unless($project->image_path && Storage::disk($diskName)->exists($project->image_path), 404);

        return Storage::disk($diskName)->response($project->image_path, $project->image_original_filename ?: basename($project->image_path), [
            'Content-Type' => $project->image_mime_type ?: 'application/octet-stream',
        ]);
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

    private function interviewQuestions(string $type, string $role): array
    {
        $questions = match ($type) {
            'technical' => [
                "Which technical skill is most important for a {$role}, and how have you used it in real work?",
                'Describe a difficult technical problem you solved. How did you investigate it and measure the result?',
                'How do you make sure your work is reliable, maintainable, and understandable for your team?',
                'Tell me about a time you received a bug report or failure. What did you do first?',
                'What would you learn first during your first 30 days in this role?',
            ],
            'behavioral' => [
                'Tell me about a time you handled a difficult situation with a teammate. What was the outcome?',
                'Describe a project where you made a mistake. What did you learn and change afterwards?',
                'Give an example of a time you took ownership without being asked.',
                'How do you prioritise when several important tasks arrive at the same time?',
                "Why are you interested in a {$role} role now?",
            ],
            'hr' => [
                "Why are you interested in this {$role} opportunity?",
                'What kind of work environment helps you do your best work?',
                'What are you looking for in your next manager and team?',
                'How do you handle feedback that you do not initially agree with?',
                'What would make this role a successful next step for you?',
            ],
            'leadership' => [
                'Tell me about a time you influenced a decision without formal authority.',
                'How do you align people when priorities conflict?',
                'Describe how you helped another person grow or succeed.',
                'How do you communicate a difficult decision to stakeholders?',
                "What leadership habit would you bring to a {$role} team?",
            ],
            'case_study' => [
                "How would you break down an unfamiliar business problem in a {$role} case study?",
                'What facts would you collect before recommending a solution?',
                'How would you decide between two viable options?',
                'How would you explain your recommendation to a non-technical stakeholder?',
                'How would you measure whether your recommendation worked?',
            ],
            default => [
                "Tell me about yourself and the path that led you toward {$role}.",
                'What achievement are you most proud of, and how did you measure its impact?',
                'What kind of team and manager help you do your best work?',
                'What is one skill you are actively improving, and what is your learning plan?',
                'What questions would you ask us before accepting this role?',
            ],
        };

        return $questions;
    }

    private function isMeaningfulInterviewAnswer(string $answer): bool
    {
        $words = collect(preg_split('/\s+/', trim($answer)) ?: [])->filter();
        $letters = preg_replace('/[^a-z]/i', '', $answer) ?: '';
        $uniqueLetters = count(array_unique(str_split(strtolower($letters))));

        return $words->count() >= 8
            && $words->unique(fn (string $word) => strtolower($word))->count() >= 5
            && mb_strlen($answer) >= 45
            && $uniqueLetters >= 5;
    }

    private function projectSkills(?string $skills): array
    {
        return collect(explode(',', (string) $skills))->map(fn (string $skill) => trim($skill))->filter()->unique()->take(20)->values()->all();
    }

    private function portfolioImage(Request $request, ?PortfolioProject $existing = null): array
    {
        if (! $request->hasFile('image')) {
            return [];
        }
        $file = $request->file('image');
        if ($existing?->image_path) {
            Storage::disk($existing->image_disk ?: 'local')->delete($existing->image_path);
        }

        return [
            'image_path' => $file->store('portfolio-images/'.$request->user()->id, 'local'),
            'image_disk' => 'local',
            'image_original_filename' => $file->getClientOriginalName(),
            'image_mime_type' => $file->getMimeType() ?: 'application/octet-stream',
        ];
    }
}
