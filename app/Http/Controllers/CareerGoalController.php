<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithWorkspaceRecords;
use App\Models\CareerGoal;
use App\Services\GroqAiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CareerGoalController extends Controller
{
    use InteractsWithWorkspaceRecords;

    public function storeGoal(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'], 'target_role' => ['nullable', 'string', 'max:255'],
            'target_industry' => ['nullable', 'string', 'max:255'], 'target_salary' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'target_date' => ['nullable', 'date'], 'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'motivation' => ['nullable', 'string', 'max:2000'], 'weekly_action' => ['nullable', 'string', 'max:500'],
        ]);
        $this->create(CareerGoal::class, 'career_goals', $data + [
            'user_id' => $request->user()->id,
            'status' => 'active',
            'progress' => $data['progress'] ?? 0,
            'milestones' => [],
        ]);

        return back()->with('status', 'Career goal saved.');
    }

    public function updateGoal(Request $request, CareerGoal $goal): RedirectResponse
    {
        $this->owns($request, $goal);
        $data = $request->validate([
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
            'weekly_action' => ['nullable', 'string', 'max:500'],
        ]);
        $this->updateAvailable($goal, 'career_goals', $data + ['status' => $data['progress'] === 100 ? 'completed' : 'active']);

        return back()->with('status', 'Goal progress updated.');
    }

    public function storeGoalMilestone(Request $request, CareerGoal $goal): RedirectResponse
    {
        $this->owns($request, $goal);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'target_date' => ['nullable', 'date'],
        ]);
        $milestones = is_array($goal->milestones) ? $goal->milestones : [];
        $milestones[] = [
            'id' => (string) Str::uuid(),
            'title' => $data['title'],
            'target_date' => $data['target_date'] ?? null,
            'status' => 'planned',
            'completed_at' => null,
        ];
        $this->updateAvailable($goal, 'career_goals', ['milestones' => $milestones]);

        return back()->with('status', 'Career milestone added.');
    }

    public function updateGoalMilestone(Request $request, CareerGoal $goal, string $milestone): RedirectResponse
    {
        $this->owns($request, $goal);
        $data = $request->validate(['status' => ['required', 'in:planned,in_progress,completed']]);
        $milestones = is_array($goal->milestones) ? $goal->milestones : [];
        $index = collect($milestones)->search(fn ($item) => is_array($item) && ($item['id'] ?? null) === $milestone);
        abort_if($index === false, 404);
        $milestones[$index]['status'] = $data['status'];
        $milestones[$index]['completed_at'] = $data['status'] === 'completed' ? now()->toDateTimeString() : null;
        $this->updateAvailable($goal, 'career_goals', ['milestones' => $milestones]);

        return back()->with('status', 'Career milestone updated.');
    }

    public function destroyGoalMilestone(Request $request, CareerGoal $goal, string $milestone): RedirectResponse
    {
        $this->owns($request, $goal);
        $milestones = collect(is_array($goal->milestones) ? $goal->milestones : []);
        abort_unless($milestones->contains(fn ($item) => is_array($item) && ($item['id'] ?? null) === $milestone), 404);
        $this->updateAvailable($goal, 'career_goals', [
            'milestones' => $milestones->reject(fn ($item) => is_array($item) && ($item['id'] ?? null) === $milestone)->values()->all(),
        ]);

        return back()->with('status', 'Career milestone removed.');
    }

    public function generateCareerAdvice(Request $request, CareerGoal $goal, GroqAiService $groq): RedirectResponse
    {
        $this->owns($request, $goal);
        $user = $request->user();
        $context = [
            'saved_skills' => $user->skills()->latest()->limit(20)->get(['name', 'category', 'proficiency', 'years_experience'])->map->only(['name', 'category', 'proficiency', 'years_experience'])->all(),
            'applications' => $user->jobApplications()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status')->all(),
            'portfolio_project_count' => $user->portfolioProjects()->count(),
            'resume_count' => $user->resumes()->count(),
            'profile_headline' => $user->careerProfile?->headline,
            'completed_milestones' => collect($goal->milestones ?? [])->where('status', 'completed')->count(),
        ];

        try {
            $advice = $groq->createCareerAdvice($goal, $context);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['career_advice' => 'SmartCV could not create career advice right now. Check your Groq setup and try again.']);
        }

        $this->updateAvailable($goal, 'career_goals', [
            'career_advice' => $advice,
            'career_advice_generated_at' => now(),
        ]);

        return back()->with('status', 'Fresh AI career advice is ready for this goal.');
    }

    public function destroyGoal(Request $request, CareerGoal $goal): RedirectResponse
    {
        $this->owns($request, $goal);
        $goal->delete();

        return back()->with('status', 'Career goal removed.');
    }
}
