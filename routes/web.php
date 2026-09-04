<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AiAnalysisController;
use App\Http\Controllers\CoverLetterController;
use App\Http\Controllers\LearningPathController;
use App\Http\Controllers\JobDiscoveryController;
use App\Http\Controllers\LiveJobBoardController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PublicPortfolioController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\ResumeBuilderController;
use App\Http\Controllers\ResumeParseController;
use App\Http\Controllers\ResumeVersionController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('home');
Route::get('/p/{slug}', [PublicPortfolioController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('portfolio.public');
Route::get('/p/{slug}/projects/{project}/image', [PublicPortfolioController::class, 'image'])->where('slug', '[a-z0-9-]+')->name('portfolio.public.image');
Route::get('/p/{slug}/resume', [PublicPortfolioController::class, 'downloadResume'])->where('slug', '[a-z0-9-]+')->name('portfolio.public.resume');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1')->name('register.store');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
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

$screens = ['dashboard', 'analyze', 'ats', 'match', 'jobs', 'interviews', 'skills', 'insights', 'portfolio', 'analytics', 'profile', 'settings', 'help', 'privacy', 'terms'];

Route::middleware(['auth', 'verified'])->group(function () use ($screens): void {
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

    Route::get('/jobs', fn (Request $request, WorkspaceController $controller) => $controller->show($request, 'jobs'))->name('jobs');
    Route::post('/jobs', [WorkspaceController::class, 'storeJob'])->name('jobs.store');
    Route::patch('/jobs/{job}', [WorkspaceController::class, 'updateJob'])->name('jobs.update');
    Route::delete('/jobs/{job}', [WorkspaceController::class, 'destroyJob'])->name('jobs.destroy');
    Route::post('/jobs/{job}/contacts', [WorkspaceController::class, 'storeJobContact'])->name('jobs.contacts.store');
    Route::delete('/jobs/{job}/contacts/{contact}', [WorkspaceController::class, 'destroyJobContact'])->name('jobs.contacts.destroy');
    Route::post('/jobs/{job}/attachments', [WorkspaceController::class, 'storeJobAttachment'])->name('jobs.attachments.store');
    Route::get('/jobs/{job}/attachments/{attachment}', [WorkspaceController::class, 'downloadJobAttachment'])->name('jobs.attachments.download');
    Route::delete('/jobs/{job}/attachments/{attachment}', [WorkspaceController::class, 'destroyJobAttachment'])->name('jobs.attachments.destroy');

    Route::get('/discover', [LiveJobBoardController::class, 'index'])->middleware('throttle:30,1')->name('discover');
    Route::post('/discover/searches', [JobDiscoveryController::class, 'store'])->name('discover.searches.store');
    Route::patch('/discover/searches/{search}', [JobDiscoveryController::class, 'update'])->name('discover.searches.update');
    Route::delete('/discover/searches/{search}', [JobDiscoveryController::class, 'destroy'])->name('discover.searches.destroy');
    Route::get('/discover/searches/{search}/open', [JobDiscoveryController::class, 'open'])->name('discover.searches.open');
    Route::post('/discover/add-to-tracker', [JobDiscoveryController::class, 'addToTracker'])->name('discover.tracker.store');
    Route::redirect('/live-jobs', '/discover')->name('live-jobs.index');

    Route::get('/interviews', fn (Request $request, WorkspaceController $controller) => $controller->show($request, 'interviews'))->name('interviews');
    Route::post('/interviews', [WorkspaceController::class, 'storeInterview'])->name('interviews.store');
    Route::patch('/interviews/{interview}/responses', [WorkspaceController::class, 'saveInterviewResponses'])->name('interviews.responses.update');
    Route::patch('/interviews/{interview}/complete', [WorkspaceController::class, 'completeInterview'])->name('interviews.complete');
    Route::post('/interviews/{interview}/recording', [WorkspaceController::class, 'storeInterviewRecording'])->name('interviews.recordings.store');
    Route::get('/interviews/{interview}/recording', [WorkspaceController::class, 'downloadInterviewRecording'])->name('interviews.recordings.download');
    Route::get('/interviews/{interview}/recording/play', [WorkspaceController::class, 'playInterviewRecording'])->name('interviews.recordings.play');
    Route::delete('/interviews/{interview}', [WorkspaceController::class, 'destroyInterview'])->name('interviews.destroy');

    Route::get('/skills', fn (Request $request, WorkspaceController $controller) => $controller->show($request, 'skills'))->name('skills');
    Route::post('/skills', [WorkspaceController::class, 'storeSkill'])->name('skills.store');
    Route::get('/skills/{skill}/certificate', [WorkspaceController::class, 'downloadSkillCertificate'])->name('skills.certificate.download');
    Route::patch('/skills/{skill}', [WorkspaceController::class, 'updateSkill'])->name('skills.update');
    Route::post('/skills/{skill}/milestones', [WorkspaceController::class, 'storeSkillMilestone'])->name('skills.milestones.store');
    Route::patch('/skills/{skill}/milestones/{milestone}', [WorkspaceController::class, 'updateSkillMilestone'])->name('skills.milestones.update');
    Route::delete('/skills/{skill}/milestones/{milestone}', [WorkspaceController::class, 'destroySkillMilestone'])->name('skills.milestones.destroy');
    Route::delete('/skills/{skill}', [WorkspaceController::class, 'destroySkill'])->name('skills.destroy');

    Route::get('/insights', fn (Request $request, WorkspaceController $controller) => $controller->show($request, 'insights'))->name('insights');
    Route::post('/goals', [WorkspaceController::class, 'storeGoal'])->name('goals.store');
    Route::patch('/goals/{goal}', [WorkspaceController::class, 'updateGoal'])->name('goals.update');
    Route::post('/goals/{goal}/milestones', [WorkspaceController::class, 'storeGoalMilestone'])->name('goals.milestones.store');
    Route::patch('/goals/{goal}/milestones/{milestone}', [WorkspaceController::class, 'updateGoalMilestone'])->name('goals.milestones.update');
    Route::delete('/goals/{goal}/milestones/{milestone}', [WorkspaceController::class, 'destroyGoalMilestone'])->name('goals.milestones.destroy');
    Route::post('/goals/{goal}/career-advice', [WorkspaceController::class, 'generateCareerAdvice'])->middleware('throttle:5,1')->name('goals.career-advice.store');
    Route::delete('/goals/{goal}', [WorkspaceController::class, 'destroyGoal'])->name('goals.destroy');

    Route::get('/learning-paths', [LearningPathController::class, 'index'])->name('learning-paths.index');
    Route::post('/learning-paths', [LearningPathController::class, 'store'])->name('learning-paths.store');
    Route::patch('/learning-path-items/{item}', [LearningPathController::class, 'updateItem'])->name('learning-path-items.update');
    Route::delete('/learning-paths/{learningPath}', [LearningPathController::class, 'destroy'])->name('learning-paths.destroy');

    Route::get('/portfolio', fn (Request $request, WorkspaceController $controller) => $controller->show($request, 'portfolio'))->name('portfolio');
    Route::post('/portfolio', [WorkspaceController::class, 'storeProject'])->name('portfolio.store');
    Route::patch('/portfolio/{project}', [WorkspaceController::class, 'updateProject'])->name('portfolio.update');
    Route::get('/portfolio/{project}/image', [WorkspaceController::class, 'showProjectImage'])->name('portfolio.image');
    Route::delete('/portfolio/{project}', [WorkspaceController::class, 'destroyProject'])->name('portfolio.destroy');
    Route::patch('/portfolio-settings', [WorkspaceController::class, 'updatePortfolioSettings'])->name('portfolio.settings.update');

    Route::get('/analytics', fn (Request $request, WorkspaceController $controller) => $controller->show($request, 'analytics'))->name('analytics');
    Route::get('/profile', fn (Request $request, WorkspaceController $controller) => $controller->show($request, 'profile'))->name('profile');
    Route::get('/settings', fn (Request $request, WorkspaceController $controller) => $controller->show($request, 'settings'))->name('settings');
    Route::patch('/profile', [WorkspaceController::class, 'updateProfile'])->name('profile.update');

    foreach (array_diff($screens, ['dashboard', 'jobs', 'interviews', 'skills', 'insights', 'portfolio', 'analytics', 'profile', 'settings']) as $screen) {
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
            $primaryResume = $requestedResume
                ? $resumes->firstWhere('id', $requestedResume)
                : $resumes->firstWhere('is_primary', true);
            $primaryResume = $primaryResume ?: $resumes->first();

            if ($primaryResume) {
                $primaryResume->load(['versions' => fn ($query) => $query->latest(), 'aiAnalyses' => fn ($query) => $query->where('status', 'completed')->latest()->limit(5)]);
            }

            return view('app', [
                'screen' => $screen,
                'primaryResume' => $primaryResume,
                'resumes' => $resumes,
                'latestAnalysis' => $primaryResume?->aiAnalyses()->where('status', 'completed')->latest()->first(),
            ]);
        })->name($screen);
    }
});
