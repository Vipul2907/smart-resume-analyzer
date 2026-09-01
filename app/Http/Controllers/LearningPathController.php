<?php

namespace App\Http\Controllers;

use App\Models\LearningPath;
use App\Models\LearningPathItem;
use App\Models\User;
use App\Services\GroqAiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class LearningPathController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('learning-paths.index', [
            'paths' => $user->learningPaths()->with('items')->latest()->get(),
            'recommendedSkills' => $this->recommendedSkills($user),
            'suggestedRole' => $this->suggestedRole($user),
        ]);
    }

    public function store(Request $request, GroqAiService $groq): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'target_role' => ['nullable', 'string', 'max:255'],
            'goal' => ['required', 'string', 'min:20', 'max:2000'],
        ]);

        $user = $request->user();
        $recommendations = $this->recommendedSkills($user);
        $targetRole = trim((string) ($data['target_role'] ?? '')) ?: null;
        $knownSkills = $user->skills()->pluck('name')->filter()->values()->all();
        $jobGaps = $user->aiAnalyses()->where('analysis_type', 'job_match')->where('status', 'completed')->latest()->limit(5)->get()
            ->flatMap(fn ($analysis) => is_array($analysis->result) ? ($analysis->result['missing_skills'] ?? []) : [])
            ->filter(fn ($skill) => is_string($skill) && trim($skill) !== '')->values()->all();

        try {
            $plan = $groq->createLearningPlan($data['goal'], $targetRole, $knownSkills, $jobGaps);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withInput()->withErrors(['goal' => 'SmartCV could not create a personalised AI learning path right now. Check your Groq setup and try again.']);
        }

        $title = trim((string) ($data['title'] ?? '')) ?: $plan['title'];

        $pathData = [
            'title' => $title,
            'target_role' => $targetRole,
            'summary' => $plan['summary'],
            'source_snapshot' => ['goal' => $data['goal'], 'skills' => $knownSkills, 'job_gaps' => $jobGaps, 'ai_generated' => true],
            'name' => $title,
            'step_number' => 1,
            'progress_percentage' => 0,
            'estimated_hours' => collect($plan['steps'])->sum('estimated_hours'),
        ];
        $pathColumns = array_flip(Schema::getColumnListing('learning_paths'));
        $path = $user->learningPaths()->create(array_intersect_key($pathData, $pathColumns));

        collect($plan['steps'])->each(function (array $step, int $index) use ($path): void {
            $path->items()->create([
                'skill_name' => $step['skill_name'],
                'title' => $step['title'],
                'description' => $step['description'],
                'estimated_hours' => $step['estimated_hours'],
                'position' => $index + 1,
            ]);
        });

        return redirect()->route('learning-paths.index')->with('status', 'Your personalised AI learning path is ready. Complete steps only when you have real evidence of progress.');
    }

    public function updateItem(Request $request, LearningPathItem $item): RedirectResponse
    {
        $this->owns($request, $item->learningPath);
        $data = $request->validate(['status' => ['required', 'in:planned,in_progress,completed']]);

        $item->update([
            'status' => $data['status'],
            'completed_at' => $data['status'] === 'completed' ? now() : null,
        ]);

        return back()->with('status', 'Learning step updated.');
    }

    public function destroy(Request $request, LearningPath $learningPath): RedirectResponse
    {
        $this->owns($request, $learningPath);
        $learningPath->delete();

        return redirect()->route('learning-paths.index')->with('status', 'Learning path removed.');
    }

    /** @return Collection<int, array{skill: string, title: string, description: string, hours: int}> */
    private function recommendedSkills(User $user): Collection
    {
        $availableColumns = array_flip(Schema::getColumnListing('skills'));
        $fields = array_values(array_intersect(['name', 'proficiency', 'target_proficiency', 'is_priority'], array_keys($availableColumns)));
        $known = $user->skills()->get($fields);
        $knownNames = $known->pluck('name')->map(fn (string $name) => mb_strtolower(trim($name)))->filter();

        $missingFromMatches = $user->aiAnalyses()
            ->where('analysis_type', 'job_match')
            ->where('status', 'completed')
            ->latest()
            ->limit(8)
            ->get(['result'])
            ->flatMap(function ($analysis): array {
                $result = is_array($analysis->result) ? $analysis->result : [];

                return collect($result['missing_skills'] ?? [])
                    ->filter(fn ($skill) => is_string($skill) && trim($skill) !== '')
                    ->map(fn (string $skill) => trim($skill))
                    ->all();
            })
            ->map(fn (string $skill) => ['skill' => $skill, 'reason' => 'This appeared as a gap in your saved job-match results.']);

        $prioritySkills = $known
            ->filter(fn ($skill) => $skill->is_priority || ($skill->target_proficiency ?? 0) > ($skill->proficiency ?? 0))
            ->map(fn ($skill) => [
                'skill' => $skill->name,
                'reason' => 'You marked this as a priority or set a higher target proficiency.',
            ]);

        $recommendations = $missingFromMatches
            ->concat($prioritySkills)
            ->unique(fn (array $item) => mb_strtolower($item['skill']))
            ->take(5)
            ->values();

        if ($recommendations->isEmpty()) {
            $recommendations = collect([
                ['skill' => 'Measurable impact', 'reason' => 'Turn your real work into clear outcomes and numbers for your resume.'],
                ['skill' => 'Role-focused portfolio', 'reason' => 'Create one focused case study that proves work relevant to your target role.'],
                ['skill' => 'Interview storytelling', 'reason' => 'Practice concise STAR stories from projects you can discuss honestly.'],
            ]);
        }

        return $recommendations->map(function (array $recommendation) use ($knownNames): array {
            $skill = $recommendation['skill'];
            $alreadyTracked = $knownNames->contains(mb_strtolower($skill));

            return [
                'skill' => $skill,
                'title' => $alreadyTracked ? 'Strengthen '.$skill : 'Build '.$skill,
                'description' => $recommendation['reason'].' Add a small project, work example, certificate, or interview story as evidence.',
                'hours' => $alreadyTracked ? 4 : 8,
            ];
        });
    }

    private function suggestedRole(User $user): ?string
    {
        return $user->careerGoals()->whereNotNull('target_role')->latest()->value('target_role')
            ?: $user->target_role;
    }

    private function owns(Request $request, LearningPath $path): void
    {
        abort_unless($path->user_id === $request->user()->id, 404);
    }
}
