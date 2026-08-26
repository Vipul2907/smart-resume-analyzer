<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Services\GroqAiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AiAnalysisController extends Controller
{
    public function store(Request $request, Resume $resume, GroqAiService $groq): RedirectResponse
    {
        if (! $request->user()->onboarding_completed_at) {
            return redirect()->route('onboarding.show');
        }

        abort_unless($resume->user_id === $request->user()->id, 404);

        $attributes = $request->validate([
            'analysis_type' => ['required', 'in:resume_review,ats_foundation'],
            'accepted_ai_privacy' => ['accepted'],
        ]);

        $analysis = $request->user()->aiAnalyses()->create([
            'resume_id' => $resume->id,
            'analysis_type' => $attributes['analysis_type'],
            'provider' => 'groq',
            'model' => config('services.groq.model'),
            'status' => 'processing',
            'prompt_version' => 'resume-foundation-v1',
            'input_snapshot' => [
                'resume_id' => $resume->id,
                'resume_name' => $resume->name,
                'text_length' => strlen((string) $resume->extracted_text),
                'privacy_accepted_at' => now()->toISOString(),
            ],
            'requested_at' => now(),
        ]);

        try {
            $groq->analyzeResume($resume, $analysis);

            return back()->with('status', 'AI analysis completed.');
        } catch (\Throwable $exception) {
            $analysis->update([
                'status' => 'failed',
                'error_message' => 'The AI provider could not complete this request. Please try again shortly.',
                'completed_at' => now(),
            ]);

            report($exception);

            return back()->with('error', 'AI analysis could not finish right now. Please check your Groq setup and try again shortly.');
        }
    }

    public function match(Request $request, Resume $resume, GroqAiService $groq): RedirectResponse
    {
        if (! $request->user()->onboarding_completed_at) {
            return redirect()->route('onboarding.show');
        }

        abort_unless($resume->user_id === $request->user()->id, 404);
        $attributes = $request->validate([
            'job_description' => ['required', 'string', 'min:80', 'max:12000'],
            'target_role' => ['nullable', 'string', 'max:255'],
            'job_application_id' => ['nullable', 'integer'],
            'accepted_ai_privacy' => ['accepted'],
        ]);
        abort_unless(! ($attributes['job_application_id'] ?? null) || $request->user()->jobApplications()->whereKey($attributes['job_application_id'])->exists(), 404);

        $analysis = $request->user()->aiAnalyses()->create([
            'resume_id' => $resume->id,
            'job_application_id' => $attributes['job_application_id'] ?? null,
            'analysis_type' => 'job_match',
            'provider' => 'groq',
            'model' => config('services.groq.model'),
            'status' => 'processing',
            'prompt_version' => 'job-match-v1',
            'input_snapshot' => [
                'resume_id' => $resume->id,
                'resume_name' => $resume->name,
                'target_role' => $attributes['target_role'] ?? null,
                'job_description' => $attributes['job_description'],
                'privacy_accepted_at' => now()->toISOString(),
            ],
            'requested_at' => now(),
        ]);

        try {
            $groq->matchJobDescription($resume, $analysis, $attributes['job_description'], $attributes['target_role'] ?? null);

            return redirect()->route('match', ['resume' => $resume->id])->with('status', 'Job match completed.');
        } catch (\Throwable $exception) {
            $analysis->update([
                'status' => 'failed',
                'error_message' => 'The AI provider could not complete this job match. Please try again shortly.',
                'completed_at' => now(),
            ]);
            report($exception);

            return back()->with('error', 'Job match could not finish right now. Please check your Groq setup and try again shortly.');
        }
    }
}
