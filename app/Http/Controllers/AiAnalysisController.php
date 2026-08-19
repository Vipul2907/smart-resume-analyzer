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

        $monthlyLimit = (int) config('services.groq.free_monthly_limit', 10);
        $monthlyUsage = $request->user()->aiAnalyses()
            ->where('provider', 'groq')
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        if ($monthlyUsage >= $monthlyLimit) {
            return back()->with('error', 'You have reached the free AI analysis limit for this month.');
        }

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
                'error_message' => $exception->getMessage(),
                'completed_at' => now(),
            ]);

            report($exception);

            return back()->with('error', 'AI analysis could not finish right now. Please check your Groq setup and try again shortly.');
        }
    }
}
