<?php

namespace Tests\Feature;

use App\Models\CareerGoal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CareerInsightsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_goal_manage_milestones_and_save_goal_specific_ai_advice(): void
    {
        config(['services.groq.key' => 'test-key']);
        Http::fake(['api.groq.com/*' => Http::response([
            'choices' => [[
                'message' => ['content' => json_encode([
                    'summary' => 'Validate the problem with local shop owners before building software.',
                    'readiness_score' => 34,
                    'next_actions' => ['Interview five potential customers.'],
                    'gaps' => ['No customer evidence saved yet.'],
                    'weekly_plan' => ['Schedule five discovery calls.'],
                ])],
            ]],
        ])]);
        $user = User::factory()->create(['email_verified_at' => now(), 'onboarding_completed_at' => now()]);

        $this->actingAs($user)->post(route('goals.store'), [
            'title' => 'Launch a local business software idea',
            'target_role' => 'Entrepreneur',
            'target_industry' => 'Retail technology',
            'target_salary' => 1200000,
            'target_date' => now()->addYear()->toDateString(),
            'motivation' => 'I want to solve a real problem for small businesses.',
            'weekly_action' => 'Talk to three local shop owners.',
            'progress' => 10,
        ])->assertSessionHas('status');

        $goal = $user->careerGoals()->firstOrFail();
        $this->actingAs($user)->post(route('goals.milestones.store', $goal), [
            'title' => 'Complete ten customer interviews',
            'target_date' => now()->addMonth()->toDateString(),
        ])->assertSessionHas('status');

        $milestone = $goal->fresh()->milestones[0];
        $this->actingAs($user)->patch(route('goals.milestones.update', [$goal, $milestone['id']]), ['status' => 'completed'])
            ->assertSessionHas('status');
        $this->actingAs($user)->post(route('goals.career-advice.store', $goal))->assertSessionHas('status');

        $savedGoal = $goal->fresh();
        $this->assertSame('Entrepreneur', $savedGoal->target_role);
        $this->assertSame('completed', $savedGoal->milestones[0]['status']);
        $this->assertSame(34, $savedGoal->career_advice['readiness_score']);
        $this->assertStringContainsString('local shop owners', $savedGoal->career_advice['summary']);
    }

    public function test_user_cannot_change_someone_elses_goal_milestone(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now(), 'onboarding_completed_at' => now()]);
        $other = User::factory()->create(['email_verified_at' => now(), 'onboarding_completed_at' => now()]);
        $goal = $owner->careerGoals()->create(['title' => 'Private goal', 'milestones' => [['id' => 'private-milestone', 'title' => 'Private step', 'status' => 'planned']]]);

        $this->actingAs($other)
            ->patch(route('goals.milestones.update', [$goal, 'private-milestone']), ['status' => 'completed'])
            ->assertNotFound();
    }
}
