<?php

use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AiAnalysisController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CareerGoalController;
use App\Http\Controllers\CoverLetterController;
use App\Http\Controllers\DocumentVaultController;
use App\Http\Controllers\HelpCenterController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\JobDiscoveryController;
use App\Http\Controllers\JobTrackerController;
use App\Http\Controllers\LearningPathController;
use App\Http\Controllers\LiveJobBoardController;
use App\Http\Controllers\NotificationCenterController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\PublicPortfolioController;
use App\Http\Controllers\ResumeBuilderController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\ResumeParseController;
use App\Http\Controllers\ResumeVersionController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Middleware\RecordUserActivity;
use App\Models\AiAnalysis;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('home');
Route::get('/p/{slug}', [PublicPortfolioController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('portfolio.public');
Route::get('/p/{slug}/projects/{project}/image', [PublicPortfolioController::class, 'image'])->where('slug', '[a-z0-9-]+')->name('portfolio.public.image');
Route::get('/p/{slug}/resume', [PublicPortfolioController::class, 'downloadResume'])->where('slug', '[a-z0-9-]+')->name('portfolio.public.resume');
Route::view('/privacy', 'legal.privacy')->name('privacy');
Route::view('/terms', 'legal.terms')->name('terms');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1')->name('register.store');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:3,1')->name('password.update');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/email/verify', fn () => view('auth.verify'))->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('onboarding.show');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'A fresh verification link has been sent.');
    })->middleware('throttle:6,1')->name('verification.send');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

$screens = ['dashboard', 'analyze', 'ats', 'match', 'jobs', 'interviews', 'skills', 'insights', 'portfolio', 'analytics', 'profile', 'settings', 'help'];

Route::middleware(['auth', 'verified', RecordUserActivity::class])->group(function () use ($screens): void {
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');

    Route::get('/resumes', [ResumeController::class, 'index'])->name('resumes');
    Route::get('/resumes/create', [ResumeBuilderController::class, 'create'])->name('resumes.builder.create');
    Route::post('/resumes/builder', [ResumeBuilderController::class, 'store'])->name('resumes.builder.store');
    Route::get('/resumes/{resume}/builder', [ResumeBuilderController::class, 'edit'])->name('resumes.builder.edit');
    Route::patch('/resumes/{resume}/builder', [ResumeBuilderController::class, 'update'])->name('resumes.builder.update');
    Route::post('/resumes/{resume}/duplicate', [ResumeBuilderController::class, 'duplicate'])->name('resumes.duplicate');
    Route::post('/resumes/{resume}/versions', [ResumeBuilderController::class, 'newVersion'])->name('resumes.versions.store');
    Route::get('/resumes/{resume}/preview', [ResumeBuilderController::class, 'preview'])->name('resumes.preview');
    Route::get('/resumes/{resume}/export/docx', [ResumeBuilderController::class, 'exportDocx'])->name('resumes.export.docx');
    Route::post('/resumes', [ResumeController::class, 'store'])->middleware('throttle:10,1')->name('resumes.store');
    Route::get('/resumes/{resume}', [ResumeController::class, 'show'])->name('resumes.show');
    Route::patch('/resumes/{resume}', [ResumeController::class, 'update'])->name('resumes.update');
    Route::delete('/resumes/{resume}', [ResumeController::class, 'destroy'])->name('resumes.destroy');
    Route::post('/resumes/{resume}/primary', [ResumeController::class, 'markPrimary'])->name('resumes.primary');
    Route::get('/resumes/{resume}/download', [ResumeController::class, 'download'])->name('resumes.download');
    Route::post('/resumes/{resume}/parse', [ResumeParseController::class, 'store'])->middleware('throttle:10,1')->name('resumes.parse');
    Route::patch('/resume-versions/{resumeVersion}', [ResumeVersionController::class, 'update'])->name('resume-versions.update');
    Route::post('/resumes/{resume}/ai-analyses', [AiAnalysisController::class, 'store'])->middleware('throttle:3,1')->name('ai-analyses.store');
    Route::post('/resumes/{resume}/job-match', [AiAnalysisController::class, 'match'])->middleware('throttle:3,1')->name('ai-matches.store');

    Route::get('/cover-letters', [CoverLetterController::class, 'index'])->name('cover-letters.index');
    Route::get('/cover-letters/create', [CoverLetterController::class, 'create'])->name('cover-letters.create');
    Route::post('/cover-letters', [CoverLetterController::class, 'store'])->name('cover-letters.store');
    Route::get('/cover-letters/{coverLetter}/edit', [CoverLetterController::class, 'edit'])->name('cover-letters.edit');
    Route::patch('/cover-letters/{coverLetter}', [CoverLetterController::class, 'update'])->name('cover-letters.update');
    Route::post('/cover-letters/{coverLetter}/duplicate', [CoverLetterController::class, 'duplicate'])->name('cover-letters.duplicate');
    Route::delete('/cover-letters/{coverLetter}', [CoverLetterController::class, 'destroy'])->name('cover-letters.destroy');
    Route::get('/cover-letters/{coverLetter}/preview', [CoverLetterController::class, 'preview'])->name('cover-letters.preview');
    Route::get('/cover-letters/{coverLetter}/download/txt', [CoverLetterController::class, 'downloadText'])->name('cover-letters.download.txt');
    Route::get('/cover-letters/{coverLetter}/download/docx', [CoverLetterController::class, 'downloadDocx'])->name('cover-letters.download.docx');

    Route::get('/dashboard', fn (Request $request, WorkspaceController $controller) => $controller->show($request, 'dashboard'))->name('dashboard');

    Route::get('/documents', [DocumentVaultController::class, 'index'])->name('documents.index');
    Route::post('/documents', [DocumentVaultController::class, 'store'])->middleware('throttle:10,1')->name('documents.store');
    Route::get('/documents/{document}/download', [DocumentVaultController::class, 'download'])->name('documents.download');
    Route::delete('/documents/{document}', [DocumentVaultController::class, 'destroy'])->name('documents.destroy');

    Route::get('/notifications', [NotificationCenterController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/refresh', [NotificationCenterController::class, 'refresh'])->name('notifications.refresh');
    Route::patch('/notifications/read-all', [NotificationCenterController::class, 'markAllRead'])->name('notifications.read-all');
    Route::patch('/notifications/{notification}/read', [NotificationCenterController::class, 'markRead'])->name('notifications.read');

    Route::get('/jobs', fn (Request $request, WorkspaceController $controller) => $controller->show($request, 'jobs'))->name('jobs');
    Route::post('/jobs', [JobTrackerController::class, 'storeJob'])->name('jobs.store');
    Route::patch('/jobs/{job}', [JobTrackerController::class, 'updateJob'])->name('jobs.update');
    Route::patch('/jobs/{job}/status', [JobTrackerController::class, 'updateJobStatus'])->name('jobs.status.update');
    Route::delete('/jobs/{job}', [JobTrackerController::class, 'destroyJob'])->name('jobs.destroy');
    Route::post('/jobs/{job}/contacts', [JobTrackerController::class, 'storeJobContact'])->name('jobs.contacts.store');
    Route::delete('/jobs/{job}/contacts/{contact}', [JobTrackerController::class, 'destroyJobContact'])->name('jobs.contacts.destroy');
    Route::post('/jobs/{job}/attachments', [JobTrackerController::class, 'storeJobAttachment'])->name('jobs.attachments.store');
    Route::get('/jobs/{job}/attachments/{attachment}', [JobTrackerController::class, 'downloadJobAttachment'])->name('jobs.attachments.download');
    Route::delete('/jobs/{job}/attachments/{attachment}', [JobTrackerController::class, 'destroyJobAttachment'])->name('jobs.attachments.destroy');

    Route::get('/discover', [LiveJobBoardController::class, 'index'])->middleware('throttle:30,1')->name('discover');
    Route::post('/discover/searches', [JobDiscoveryController::class, 'store'])->name('discover.searches.store');
    Route::patch('/discover/searches/{search}', [JobDiscoveryController::class, 'update'])->name('discover.searches.update');
    Route::delete('/discover/searches/{search}', [JobDiscoveryController::class, 'destroy'])->name('discover.searches.destroy');
    Route::get('/discover/searches/{search}/open', [JobDiscoveryController::class, 'open'])->name('discover.searches.open');
    Route::post('/discover/add-to-tracker', [JobDiscoveryController::class, 'addToTracker'])->name('discover.tracker.store');
    Route::redirect('/live-jobs', '/discover')->name('live-jobs.index');

    Route::get('/interviews', fn (Request $request, WorkspaceController $controller) => $controller->show($request, 'interviews'))->name('interviews');
    Route::post('/interviews', [InterviewController::class, 'storeInterview'])->name('interviews.store');
    Route::patch('/interviews/{interview}/responses', [InterviewController::class, 'saveInterviewResponses'])->name('interviews.responses.update');
    Route::patch('/interviews/{interview}/complete', [InterviewController::class, 'completeInterview'])->name('interviews.complete');
    Route::post('/interviews/{interview}/recording', [InterviewController::class, 'storeInterviewRecording'])->name('interviews.recordings.store');
    Route::get('/interviews/{interview}/recording', [InterviewController::class, 'downloadInterviewRecording'])->name('interviews.recordings.download');
    Route::get('/interviews/{interview}/recording/play', [InterviewController::class, 'playInterviewRecording'])->name('interviews.recordings.play');
    Route::delete('/interviews/{interview}', [InterviewController::class, 'destroyInterview'])->name('interviews.destroy');

    Route::get('/skills', fn (Request $request, WorkspaceController $controller) => $controller->show($request, 'skills'))->name('skills');
    Route::post('/skills', [SkillController::class, 'storeSkill'])->name('skills.store');
    Route::get('/skills/{skill}/certificate', [SkillController::class, 'downloadSkillCertificate'])->name('skills.certificate.download');
    Route::patch('/skills/{skill}', [SkillController::class, 'updateSkill'])->name('skills.update');
    Route::post('/skills/{skill}/milestones', [SkillController::class, 'storeSkillMilestone'])->name('skills.milestones.store');
    Route::patch('/skills/{skill}/milestones/{milestone}', [SkillController::class, 'updateSkillMilestone'])->name('skills.milestones.update');
    Route::delete('/skills/{skill}/milestones/{milestone}', [SkillController::class, 'destroySkillMilestone'])->name('skills.milestones.destroy');
    Route::delete('/skills/{skill}', [SkillController::class, 'destroySkill'])->name('skills.destroy');

    Route::get('/insights', fn (Request $request, WorkspaceController $controller) => $controller->show($request, 'insights'))->name('insights');
    Route::post('/goals', [CareerGoalController::class, 'storeGoal'])->name('goals.store');
    Route::patch('/goals/{goal}', [CareerGoalController::class, 'updateGoal'])->name('goals.update');
    Route::post('/goals/{goal}/milestones', [CareerGoalController::class, 'storeGoalMilestone'])->name('goals.milestones.store');
    Route::patch('/goals/{goal}/milestones/{milestone}', [CareerGoalController::class, 'updateGoalMilestone'])->name('goals.milestones.update');
    Route::delete('/goals/{goal}/milestones/{milestone}', [CareerGoalController::class, 'destroyGoalMilestone'])->name('goals.milestones.destroy');
    Route::post('/goals/{goal}/career-advice', [CareerGoalController::class, 'generateCareerAdvice'])->middleware('throttle:5,1')->name('goals.career-advice.store');
    Route::delete('/goals/{goal}', [CareerGoalController::class, 'destroyGoal'])->name('goals.destroy');

    Route::get('/learning-paths', [LearningPathController::class, 'index'])->name('learning-paths.index');
    Route::post('/learning-paths', [LearningPathController::class, 'store'])->name('learning-paths.store');
    Route::patch('/learning-path-items/{item}', [LearningPathController::class, 'updateItem'])->name('learning-path-items.update');
    Route::delete('/learning-paths/{learningPath}', [LearningPathController::class, 'destroy'])->name('learning-paths.destroy');

    Route::get('/portfolio', fn (Request $request, WorkspaceController $controller) => $controller->show($request, 'portfolio'))->name('portfolio');
    Route::post('/portfolio', [PortfolioController::class, 'storeProject'])->name('portfolio.store');
    Route::patch('/portfolio/{project}', [PortfolioController::class, 'updateProject'])->name('portfolio.update');
    Route::get('/portfolio/{project}/image', [PortfolioController::class, 'showProjectImage'])->name('portfolio.image');
    Route::delete('/portfolio/{project}', [PortfolioController::class, 'destroyProject'])->name('portfolio.destroy');
    Route::patch('/portfolio-settings', [PortfolioController::class, 'updatePortfolioSettings'])->name('portfolio.settings.update');

    Route::get('/help', [HelpCenterController::class, 'index'])->name('help');
    Route::post('/help/requests', [HelpCenterController::class, 'store'])->middleware('throttle:5,1')->name('help.requests.store');

    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/users/{user}', [AdminController::class, 'showUser'])->name('admin.users.show');
    Route::patch('/admin/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::patch('/admin/support-requests/{supportRequest}', [AdminController::class, 'updateTicket'])->name('admin.tickets.update');
    Route::post('/admin/announcements', [AdminController::class, 'storeAnnouncement'])->name('admin.announcements.store');
    Route::delete('/admin/announcements/{announcement}', [AdminController::class, 'destroyAnnouncement'])->name('admin.announcements.destroy');

    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
    Route::get('/analytics/print', [AnalyticsController::class, 'printable'])->name('analytics.print');
    Route::get('/analytics/export/applications', [AnalyticsController::class, 'exportApplications'])->name('analytics.applications.export');
    Route::get('/analytics/export/data', [AnalyticsController::class, 'exportPersonalData'])->name('analytics.data.export');
    Route::get('/analytics/recruiter-report', [AnalyticsController::class, 'recruiterReport'])->name('analytics.recruiter-report');
    Route::get('/profile', fn (Request $request, WorkspaceController $controller) => $controller->show($request, 'profile'))->name('profile');
    Route::get('/settings', [AccountSettingsController::class, 'index'])->name('settings');
    Route::patch('/settings/preferences', [AccountSettingsController::class, 'updatePreferences'])->name('settings.preferences.update');
    Route::patch('/settings/password', [AccountSettingsController::class, 'updatePassword'])->name('settings.password.update');
    Route::delete('/settings/account', [AccountSettingsController::class, 'destroy'])->name('settings.destroy');
    Route::patch('/profile', [WorkspaceController::class, 'updateProfile'])->name('profile.update');

    foreach (array_diff($screens, ['dashboard', 'jobs', 'interviews', 'skills', 'insights', 'portfolio', 'analytics', 'profile', 'settings', 'help']) as $screen) {
        Route::get("/{$screen}", function (Request $request) use ($screen) {
            if (! request()->user()->onboarding_completed_at && $screen !== 'onboarding') {
                return redirect()->route('onboarding.show');
            }

            $user = $request->user();
            $resumes = $user->resumes()
                ->orderByDesc('is_primary')
                ->latest()
                ->get();
            $requestedResume = $request->integer('resume');
            if ($requestedResume > 0) {
                $primaryResume = $resumes->firstWhere('id', $requestedResume);
                abort_unless($primaryResume, 404);
            } else {
                $primaryResume = $resumes->firstWhere('is_primary', true) ?: $resumes->first();
            }

            if ($primaryResume) {
                $primaryResume->load(['versions' => fn ($query) => $query->latest(), 'aiAnalyses' => fn ($query) => $query->where('status', 'completed')->latest()->limit(5)]);
            }

            $latestAnalysis = $primaryResume?->aiAnalyses()->where('status', 'completed')->latest()->first();
            $transientResult = $request->session()->get('transient_ai_result');
            if (is_array($transientResult)
                && $primaryResume
                && (int) ($transientResult['resume_id'] ?? 0) === $primaryResume->id
                && ($transientResult['analysis_type'] ?? '') !== 'job_match') {
                $latestAnalysis = new AiAnalysis([
                    'resume_id' => $primaryResume->id,
                    'analysis_type' => $transientResult['analysis_type'],
                    'status' => $transientResult['status'],
                    'result' => $transientResult['result'],
                    'score' => $transientResult['score'],
                    'completed_at' => $transientResult['completed_at'],
                ]);
                $latestAnalysis->created_at = now();
            }

            return view('app', [
                'screen' => $screen,
                'primaryResume' => $primaryResume,
                'resumes' => $resumes,
                'latestAnalysis' => $latestAnalysis,
            ]);
        })->name($screen);
    }
});
