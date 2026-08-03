<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AiAnalysisController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\ResumeParseController;
use App\Http\Controllers\ResumeVersionController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('home');

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

$screens = ['dashboard', 'analyze', 'ats', 'jobs', 'interviews', 'skills', 'insights', 'portfolio', 'analytics', 'profile', 'settings', 'help', 'privacy', 'terms'];

Route::middleware(['auth', 'verified'])->group(function () use ($screens): void {
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');

    Route::get('/resumes', [ResumeController::class, 'index'])->name('resumes');
    Route::post('/resumes', [ResumeController::class, 'store'])->middleware('throttle:10,1')->name('resumes.store');
    Route::get('/resumes/{resume}', [ResumeController::class, 'show'])->name('resumes.show');
    Route::patch('/resumes/{resume}', [ResumeController::class, 'update'])->name('resumes.update');
    Route::delete('/resumes/{resume}', [ResumeController::class, 'destroy'])->name('resumes.destroy');
    Route::post('/resumes/{resume}/primary', [ResumeController::class, 'markPrimary'])->name('resumes.primary');
    Route::get('/resumes/{resume}/download', [ResumeController::class, 'download'])->name('resumes.download');
    Route::post('/resumes/{resume}/parse', [ResumeParseController::class, 'store'])->middleware('throttle:10,1')->name('resumes.parse');
    Route::patch('/resume-versions/{resumeVersion}', [ResumeVersionController::class, 'update'])->name('resume-versions.update');
    Route::post('/resumes/{resume}/ai-analyses', [AiAnalysisController::class, 'store'])->middleware('throttle:3,1')->name('ai-analyses.store');

    foreach ($screens as $screen) {
        Route::get("/{$screen}", function () use ($screen) {
            if (! request()->user()->onboarding_completed_at && $screen !== 'onboarding') {
                return redirect()->route('onboarding.show');
            }

            $user = request()->user();
            $primaryResume = $user->resumes()->with(['versions' => fn ($query) => $query->latest(), 'aiAnalyses' => fn ($query) => $query->latest()->limit(5)])->where('is_primary', true)->first()
                ?: $user->resumes()->with(['versions' => fn ($query) => $query->latest(), 'aiAnalyses' => fn ($query) => $query->latest()->limit(5)])->latest()->first();

            return view('app', [
                'screen' => $screen,
                'primaryResume' => $primaryResume,
                'latestAnalysis' => $primaryResume?->aiAnalyses()->latest()->first(),
            ]);
        })->name($screen);
    }
});
