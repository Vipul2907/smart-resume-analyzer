<?php

namespace App\Services;

use App\Models\AiAnalysis;
use App\Models\Resume;
use App\Models\InterviewSession;
use App\Models\CareerGoal;
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
        $result = $this->resumeReviewResult($this->decodedResult($content));

        $analysis->update([
            'status' => 'completed',
            'provider' => 'groq',
            'model' => $model,
            'result' => $result,
            'score' => $result['score'],
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
        $result = $this->jobMatchResult($this->decodedResult($content));

        $analysis->update([
            'status' => 'completed',
            'provider' => 'groq',
            'model' => $model,
            'result' => $result,
            'score' => $result['score'],
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

    /**
     * Create advice for one saved career goal. It deliberately uses the goal
     * before any saved technical data, so a founder is not pushed into an
     * unrelated engineering plan.
     *
     * @return array{summary: string, readiness_score: int, next_actions: array<int, string>, gaps: array<int, string>, weekly_plan: array<int, string>}
     * @throws RequestException
     */
    public function createCareerAdvice(CareerGoal $goal, array $context): array
    {
        $result = $this->jsonCompletion(
            'You are a practical and honest career coach. Return JSON only. Personalise every recommendation to the stated goal. Do not invent market data, achievements, or qualifications.',
            'Career goal: '.$goal->title."\n".
            'Target role: '.($goal->target_role ?: 'not specified')."\n".
            'Target industry: '.($goal->target_industry ?: 'not specified')."\n".
            'Why it matters: '.($goal->motivation ?: 'not specified')."\n".
            'Target date: '.($goal->target_date?->toDateString() ?: 'not specified')."\n".
            'Current saved context: '.$this->json($context)."\n\n".
            'Return JSON exactly with summary (string), readiness_score (0-100 integer), next_actions (3 concise, practical actions), gaps (up to 4 truthful gaps), and weekly_plan (3 or 4 specific actions for the next seven days). For entrepreneurship goals, prioritise customer discovery, validation, sales, finance, and execution. Do not suggest unrelated developer skills unless the user goal makes them relevant.'
        );

        return [
            'summary' => str($result['summary'] ?? 'A focused review of your saved career goal.')->limit(2000)->trim()->toString(),
            'readiness_score' => max(0, min(100, (int) ($result['readiness_score'] ?? 0))),
            'next_actions' => $this->strings($result['next_actions'] ?? []),
            'gaps' => $this->strings($result['gaps'] ?? []),
            'weekly_plan' => $this->strings($result['weekly_plan'] ?? []),
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
        return $this->decodedResult($content);
    }

    /** @return array<string, mixed> */
    private function decodedResult(mixed $content): array
    {
        $decoded = json_decode(is_string($content) ? $content : '{}', true);
        if (! is_array($decoded)) {
            throw new RuntimeException('The AI returned an invalid response. Please try again.');
        }

        return $decoded;
    }

    /** @param array<string, mixed> $result
     *  @return array<string, mixed>
     */
    private function resumeReviewResult(array $result): array
    {
        return [
            'score' => $this->score($result['score'] ?? null),
            'strengths' => $this->strings($result['strengths'] ?? []),
            'weaknesses' => $this->strings($result['weaknesses'] ?? []),
            'missing_sections' => $this->strings($result['missing_sections'] ?? []),
            'next_actions' => $this->strings($result['next_actions'] ?? []),
            'score_note' => 'This is SmartCV AI guidance based on the uploaded resume. It is not an official ATS score or a guarantee of an employer outcome.',
        ];
    }

    /** @param array<string, mixed> $result
     *  @return array<string, mixed>
     */
    private function jobMatchResult(array $result): array
    {
        return [
            'score' => $this->score($result['score'] ?? null),
            'summary' => $this->string($result['summary'] ?? '', 2000),
            'matching_skills' => $this->strings($result['matching_skills'] ?? []),
            'missing_skills' => $this->strings($result['missing_skills'] ?? []),
            'keyword_suggestions' => $this->strings($result['keyword_suggestions'] ?? []),
            'resume_improvements' => $this->strings($result['resume_improvements'] ?? []),
            'interview_questions' => $this->strings($result['interview_questions'] ?? []),
            'next_actions' => $this->strings($result['next_actions'] ?? []),
            'role_recommendation' => $this->string($result['role_recommendation'] ?? '', 1000),
            'score_note' => 'This is a SmartCV AI guidance score based on the supplied resume and job description. It is not an official ATS score or a hiring prediction.',
        ];
    }

    private function score(mixed $value): int
    {
        return max(0, min(100, (int) $value));
    }

    private function string(mixed $value, int $limit): string
    {
        return is_string($value) ? str($value)->limit($limit)->trim()->toString() : '';
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
