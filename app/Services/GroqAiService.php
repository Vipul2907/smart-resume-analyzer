<?php

namespace App\Services;

use App\Models\AiAnalysis;
use App\Models\Resume;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GroqAiService
{
    /**
     * @throws RequestException
     */
    public function analyzeResume(Resume $resume, AiAnalysis $analysis): AiAnalysis
    {
        $apiKey = config('services.groq.key');

        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('Groq is not configured yet. Add GROQ_API_KEY to your .env file.');
        }

        if (! is_string($resume->extracted_text) || trim($resume->extracted_text) === '') {
            throw new RuntimeException('Parse this resume before sending it to AI.');
        }

        $model = (string) config('services.groq.model');
        $prompt = $this->prompt($resume);

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout((int) config('services.groq.timeout', 30))
            ->retry(2, 250)
            ->post(rtrim((string) config('services.groq.base_url'), '/').'/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a careful resume review assistant. Return concise JSON only.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.2,
                'response_format' => ['type' => 'json_object'],
            ])
            ->throw();

        $payload = $response->json();
        $content = data_get($payload, 'choices.0.message.content', '{}');
        $result = json_decode(is_string($content) ? $content : '{}', true);

        if (! is_array($result)) {
            $result = ['summary' => $content];
        }

        $analysis->update([
            'status' => 'completed',
            'provider' => 'groq',
            'model' => $model,
            'result' => $result,
            'score' => isset($result['score']) ? (int) $result['score'] : null,
            'input_tokens' => data_get($payload, 'usage.prompt_tokens'),
            'output_tokens' => data_get($payload, 'usage.completion_tokens'),
            'completed_at' => now(),
        ]);

        $resume->update(['last_analyzed_at' => now()]);

        return $analysis->refresh();
    }

    /**
     * Compare one private resume with a job description and save structured career guidance.
     *
     * @throws RequestException
     */
    public function matchJobDescription(Resume $resume, AiAnalysis $analysis, string $jobDescription, ?string $targetRole = null): AiAnalysis
    {
        $apiKey = config('services.groq.key');

        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('Groq is not configured yet. Add GROQ_API_KEY to your .env file.');
        }

        if (! is_string($resume->extracted_text) || trim($resume->extracted_text) === '') {
            throw new RuntimeException('Parse this resume before sending it to AI.');
        }

        $model = (string) config('services.groq.model');
        $resumeText = str($resume->extracted_text)->limit(12000, '')->toString();
        $description = str($jobDescription)->limit(12000, '')->toString();
        $role = $targetRole ?: 'the target role';
        $prompt = <<<PROMPT
Compare this resume against the job description for {$role}. Return JSON only with these keys:
score (0-100 integer), summary (string), matching_skills (array), missing_skills (array), keyword_suggestions (array), resume_improvements (array), interview_questions (array of five questions), next_actions (array), role_recommendation (string).

Resume:
{$resumeText}

Job description:
{$description}
PROMPT;

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout((int) config('services.groq.timeout', 30))
            ->retry(2, 250)
            ->post(rtrim((string) config('services.groq.base_url'), '/').'/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a careful career coach. Give truthful, concise, structured guidance. Return JSON only.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.2,
                'response_format' => ['type' => 'json_object'],
            ])
            ->throw();

        $payload = $response->json();
        $content = data_get($payload, 'choices.0.message.content', '{}');
        $result = json_decode(is_string($content) ? $content : '{}', true);

        if (! is_array($result)) {
            $result = ['summary' => (string) $content];
        }

        $analysis->update([
            'status' => 'completed',
            'provider' => 'groq',
            'model' => $model,
            'result' => $result,
            'score' => isset($result['score']) ? max(0, min(100, (int) $result['score'])) : null,
            'input_tokens' => data_get($payload, 'usage.prompt_tokens'),
            'output_tokens' => data_get($payload, 'usage.completion_tokens'),
            'completed_at' => now(),
        ]);

        $resume->update(['last_analyzed_at' => now()]);

        return $analysis->refresh();
    }

    private function prompt(Resume $resume): string
    {
        $text = str($resume->extracted_text)->limit(12000, '')->toString();

        return <<<PROMPT
Review this resume and return JSON with keys:
score (0-100), strengths (array), weaknesses (array), missing_sections (array), next_actions (array).

Resume:
{$text}
PROMPT;
    }
}
