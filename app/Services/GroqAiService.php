<?php

namespace App\Services;

use App\Models\AiAnalysis;
use App\Models\Resume;
use App\Models\InterviewSession;
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

    /**
     * Create a role- and goal-specific learning plan. This method deliberately
     * receives only the user's goal and the career data needed for suggestions.
     *
     * @return array{title: string, summary: string, steps: array<int, array{skill_name: string, title: string, description: string, estimated_hours: int}>}
     * @throws RequestException
     */
    public function createLearningPlan(string $goal, ?string $targetRole, array $knownSkills, array $jobGaps): array
    {
        $result = $this->jsonCompletion(
            'You are a practical career-learning planner. Return JSON only. Never assume experience the user did not state. Ignore technical job gaps that are irrelevant to the stated goal.',
            <<<PROMPT
Create a realistic, personalised learning plan for this user.

User goal: {$goal}
Target role, if any: {$targetRole}
Already tracked skills: {$this->json($knownSkills)}
Past job-match gaps (use only if relevant): {$this->json($jobGaps)}

Return JSON exactly in this shape:
{
  "title": "short plan title",
  "summary": "one honest sentence about the plan",
  "steps": [
    {"skill_name": "area", "title": "actionable learning step", "description": "specific practical outcome or proof to create", "estimated_hours": 1}
  ]
}

Give 4 or 5 steps. For an entrepreneur goal, focus on customer discovery, validation, sales, finance, product execution, or other relevant business skills — not unrelated REST APIs or developer tooling.
PROMPT
        );

        $steps = collect($result['steps'] ?? [])
            ->filter(fn ($step) => is_array($step) && filled($step['skill_name'] ?? null) && filled($step['title'] ?? null))
            ->take(5)
            ->map(fn (array $step) => [
                'skill_name' => str($step['skill_name'])->limit(100)->trim()->toString(),
                'title' => str($step['title'])->limit(255)->trim()->toString(),
                'description' => str($step['description'] ?? 'Create practical evidence that shows this progress.')->limit(1500)->trim()->toString(),
                'estimated_hours' => max(1, min(100, (int) ($step['estimated_hours'] ?? 4))),
            ])
            ->values()
            ->all();

        if (count($steps) < 3) {
            throw new RuntimeException('The AI response did not include enough learning steps. Please try again.');
        }

        return [
            'title' => str($result['title'] ?? 'Personal learning plan')->limit(255)->trim()->toString(),
            'summary' => str($result['summary'] ?? 'A focused plan based on your goal.')->limit(2000)->trim()->toString(),
            'steps' => $steps,
        ];
    }

    /**
     * Evaluate the actual saved interview answers. The caller rejects obvious
     * nonsense first, while Groq checks relevance, clarity, examples, and impact.
     *
     * @return array{score: int, strengths: array, improvements: array}
     * @throws RequestException
     */
    public function evaluateInterviewResponses(InterviewSession $interview): array
    {
        $questions = is_array($interview->questions) ? $interview->questions : [];
        $answers = is_array($interview->responses) ? $interview->responses : [];
        $result = $this->jsonCompletion(
            'You are a strict interview coach. Return JSON only. Give a score of 0 for gibberish, repeated filler, or answers that do not address the question. Do not reward length alone.',
            'Target role: '.($interview->target_role ?: 'not provided')."\n".
            'Interview type: '.($interview->session_type ?: $interview->type ?: 'general')."\n".
            'Questions: '.$this->json($questions)."\n".
            'Answers: '.$this->json($answers)."\n\n".
            'Return JSON with score (0-100 integer), strengths (array of concise strings), and improvements (array of concise strings). Score relevance, specificity, clarity, examples, and measurable outcomes. If answers are weak, explain honestly.'
        );

        return [
            'score' => max(0, min(100, (int) ($result['score'] ?? 0))),
            'strengths' => $this->strings($result['strengths'] ?? []),
            'improvements' => $this->strings($result['improvements'] ?? []),
        ];
    }

    /** @return array<string, mixed> */
    private function jsonCompletion(string $system, string $prompt): array
    {
        $apiKey = config('services.groq.key');
        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('Groq is not configured yet. Add GROQ_API_KEY to your .env file.');
        }

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout((int) config('services.groq.timeout', 30))
            ->retry(2, 250)
            ->post(rtrim((string) config('services.groq.base_url'), '/').'/chat/completions', [
                'model' => (string) config('services.groq.model'),
                'messages' => [['role' => 'system', 'content' => $system], ['role' => 'user', 'content' => $prompt]],
                'temperature' => 0.2,
                'response_format' => ['type' => 'json_object'],
            ])
            ->throw();

        $content = data_get($response->json(), 'choices.0.message.content', '{}');
        $decoded = json_decode(is_string($content) ? $content : '{}', true);
        if (! is_array($decoded)) {
            throw new RuntimeException('The AI returned an invalid response. Please try again.');
        }

        return $decoded;
    }

    /** @return array<int, string> */
    private function strings(mixed $values): array
    {
        return collect(is_array($values) ? $values : [])
            ->filter(fn ($value) => is_string($value) && trim($value) !== '')
            ->map(fn (string $value) => str($value)->limit(500)->trim()->toString())
            ->values()
            ->all();
    }

    private function json(mixed $value): string
    {
        return (string) json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
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
