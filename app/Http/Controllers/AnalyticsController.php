<?php

namespace App\Http\Controllers;

use App\Models\InterviewSession;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    /**
     * Show a private report made only from the signed-in user's saved data.
     */
    public function index(Request $request): View
    {
        return view('analytics.index', $this->reportData($request));
    }

    /**
     * A printer-friendly report. The browser print dialog can save it as a PDF.
     */
    public function printable(Request $request): View
    {
        return view('analytics.print', $this->reportData($request));
    }

    /**
     * Export the user's job tracker entries as a standards-friendly CSV file.
     */
    public function exportApplications(Request $request): StreamedResponse
    {
        $jobs = $request->user()->jobApplications()->latest()->get();
        $filename = 'smartcv-applications-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($jobs): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Company', 'Role', 'Status', 'Location', 'Work mode', 'Source', 'Applied date', 'Follow-up date', 'Job URL', 'Notes', 'Created at']);

            foreach ($jobs as $job) {
                fputcsv($handle, [
                    $job->company,
                    $job->role,
                    $job->status,
                    $job->location,
                    $job->work_mode,
                    $job->source,
                    $job->applied_at?->format('Y-m-d') ?? $job->application_date?->format('Y-m-d'),
                    $job->follow_up_at?->format('Y-m-d'),
                    $job->job_url,
                    $job->notes,
                    $job->created_at?->toDateTimeString(),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Give the account owner a machine-readable copy of their saved workspace data.
     * File contents are intentionally excluded; this export contains their records
     * and metadata, not private uploaded documents.
     */
    public function exportPersonalData(Request $request): StreamedResponse
    {
        $user = $request->user();
        $payload = [
            'exported_at' => now()->toIso8601String(),
            'notice' => 'This export contains private SmartCV records. Keep it secure.',
            'account' => $user->only(['name', 'email', 'target_role', 'experience_level', 'created_at']),
            'career_profile' => $user->careerProfile?->toArray(),
            'resumes' => $user->resumes()->get()->map(fn ($resume) => $resume->only(['id', 'name', 'original_filename', 'mime_type', 'file_size', 'parse_status', 'is_primary', 'last_analyzed_at', 'created_at'])),
            'job_applications' => $user->jobApplications()->get()->toArray(),
            'interview_sessions' => $user->interviewSessions()->get()->toArray(),
            'skills' => $user->skills()->get()->toArray(),
            'career_goals' => $user->careerGoals()->get()->toArray(),
            'portfolio_projects' => $user->portfolioProjects()->get()->toArray(),
            'cover_letters' => $user->coverLetters()->get()->toArray(),
            'learning_paths' => $user->learningPaths()->with('items')->get()->toArray(),
            'ai_analyses' => $user->aiAnalyses()->get()->toArray(),
        ];

        return response()->streamDownload(function () use ($payload): void {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }, 'smartcv-personal-data-'.now()->format('Y-m-d').'.json', ['Content-Type' => 'application/json; charset=UTF-8']);
    }

    /**
     * A focused printable profile suitable for sharing with a recruiter.
     */
    public function recruiterReport(Request $request): View
    {
        $user = $request->user();
        $resume = $user->resumes()->where('is_primary', true)->first() ?: $user->resumes()->latest()->first();
        $projects = $user->portfolioProjects();

        // Older SmartCV databases did not store a project status. Do not exclude
        // a user's projects, or fail the report, simply because that optional
        // column is not present yet.
        if (Schema::hasColumn('portfolio_projects', 'status')) {
            $projects->where('status', 'completed');
        }

        if (Schema::hasColumn('portfolio_projects', 'is_featured')) {
            $projects->orderByDesc('is_featured');
        }

        return view('analytics.recruiter-report', [
            'profile' => $user->careerProfile,
            'resume' => $resume,
            'skills' => $user->skills()->orderByDesc('proficiency')->limit(10)->get(),
            'projects' => $projects->latest()->limit(6)->get(),
            'latestScore' => $user->aiAnalyses()->where('status', 'completed')->whereNotNull('score')->latest()->value('score'),
            'generatedAt' => now(),
        ]);
    }

    /**
     * Keep calculations in one place so the screen and printable report agree.
     * No platform-wide data is ever included in this report.
     *
     * @return array<string, mixed>
     */
    private function reportData(Request $request): array
    {
        $user = $request->user();
        $jobs = $user->jobApplications()->get();
        $interviews = $user->interviewSessions()->get();
        $skills = $user->skills()->get();
        $goals = $user->careerGoals()->get();
        $analyses = $user->aiAnalyses()->where('status', 'completed')->get();

        $statusOrder = ['saved', 'applied', 'interviewing', 'offer', 'rejected', 'withdrawn'];
        $statuses = collect($statusOrder)->map(function (string $status) use ($jobs): array {
            $count = $jobs->where('status', $status)->count();

            return [
                'key' => $status,
                'label' => ucfirst($status),
                'count' => $count,
            ];
        });

        $months = collect(range(5, 0))->map(function (int $offset) use ($jobs): array {
            $month = now()->subMonths($offset)->startOfMonth();

            return [
                'label' => $month->format('M'),
                'applications' => $jobs->filter(fn (JobApplication $job) => $job->created_at?->isSameMonth($month))->count(),
            ];
        });

        $interviewScores = $interviews->map(function (InterviewSession $session): ?float {
            $score = $session->score ?? $session->overall_score;

            return is_numeric($score) ? (float) $score : null;
        })->filter(fn (?float $score) => $score !== null)->values();

        $completedInterviews = $interviews->where('status', 'completed')->count();
        $responded = $jobs->whereIn('status', ['interviewing', 'offer'])->count();
        $active = $jobs->whereIn('status', ['saved', 'applied', 'interviewing', 'offer'])->count();
        $applicationCount = $jobs->count();
        $skillAverage = $skills->filter(fn ($skill) => is_numeric($skill->proficiency))->avg('proficiency');
        $goalAverage = $goals->filter(fn ($goal) => is_numeric($goal->progress))->avg('progress');
        $latestResumeScore = $analyses->filter(fn ($analysis) => is_numeric($analysis->score))->sortByDesc('created_at')->first()?->score;

        $sources = $jobs
            ->groupBy(fn (JobApplication $job) => trim((string) ($job->source ?: 'Manual entry')) ?: 'Manual entry')
            ->map(function ($sourceJobs, string $source): array {
                $total = $sourceJobs->count();
                $converted = $sourceJobs->whereIn('status', ['interviewing', 'offer'])->count();

                return [
                    'name' => $source,
                    'total' => $total,
                    'converted' => $converted,
                    'rate' => $total ? (int) round(($converted / $total) * 100) : 0,
                ];
            })
            ->sortByDesc('total')
            ->take(5)
            ->values();

        $analysisHistory = $analyses
            ->filter(fn ($analysis) => is_numeric($analysis->score))
            ->sortBy('created_at')
            ->take(-8)
            ->values()
            ->map(fn ($analysis): array => [
                'date' => $analysis->created_at?->format('M j') ?? 'Saved',
                'score' => (int) $analysis->score,
                'type' => str_replace('_', ' ', $analysis->analysis_type),
            ]);

        $topSkills = $skills
            ->sortByDesc(fn ($skill) => (int) ($skill->proficiency ?? 0))
            ->take(5)
            ->map(fn ($skill): array => [
                'name' => $skill->name,
                'proficiency' => (int) ($skill->proficiency ?? 0),
                'target' => (int) ($skill->target_proficiency ?? 0),
            ]);

        return [
            'metrics' => [
                'applications' => $applicationCount,
                'active' => $active,
                'response_rate' => $applicationCount ? (int) round(($responded / $applicationCount) * 100) : 0,
                'offer_rate' => $applicationCount ? (int) round(($jobs->where('status', 'offer')->count() / $applicationCount) * 100) : 0,
                'interviews' => $interviews->count(),
                'completed_interviews' => $completedInterviews,
                'interview_score' => $interviewScores->isNotEmpty() ? (int) round($interviewScores->avg()) : null,
                'skills' => $skills->count(),
                'skill_average' => $skillAverage !== null ? (int) round($skillAverage) : null,
                'goals' => $goals->count(),
                'goal_average' => $goalAverage !== null ? (int) round($goalAverage) : null,
                'projects' => $user->portfolioProjects()->count(),
                'resume_score' => is_numeric($latestResumeScore) ? (int) $latestResumeScore : null,
            ],
            'statuses' => $statuses,
            'months' => $months,
            'sources' => $sources,
            'analysisHistory' => $analysisHistory,
            'topSkills' => $topSkills,
            'goals' => $goals->sortByDesc('updated_at')->take(5),
            'generatedAt' => Carbon::now(),
        ];
    }
}
