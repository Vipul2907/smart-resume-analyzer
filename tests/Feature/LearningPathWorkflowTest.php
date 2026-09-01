<?php

namespace Tests\Feature;

use App\Models\AiAnalysis;
use App\Models\LearningPath;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LearningPathWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_and_complete_a_learning_path_from_real_job_match_gaps(): void
    {
        config(['services.groq.key' => 'test-key']);
        $plan = ['title' => 'Founder validation plan', 'summary' => 'A practical founder plan.', 'steps' => [['skill_name' => 'Customer discovery', 'title' => 'Interview potential customers', 'description' => 'Speak with ten local shop owners.', 'estimated_hours' => 8], ['skill_name' => 'Validation', 'title' => 'Test a simple offer', 'description' => 'Create and test one landing page.', 'estimated_hours' => 6], ['skill_name' => 'Sales', 'title' => 'Run a small sales test', 'description' => 'Ask for a paid pilot.', 'estimated_hours' => 8]]];
        $responsePayload = [
            'choices' => [[
                'message' => ['content' => json_encode($plan)],
            ]],
        ];
        Http::fake(['api.groq.com/*' => Http::response($responsePayload)]);
        $user = User::factory()->create(['email_verified_at' => now(), 'onboarding_completed_at' => now(), 'target_role' => 'Backend Engineer']);
        AiAnalysis::create([
            'user_id' => $user->id,
            'analysis_type' => 'job_match',
            'status' => 'completed',
            'result' => ['missing_skills' => ['Docker', 'Testing']],
        ]);

        $this->actingAs($user)
            ->post(route('learning-paths.store'), ['target_role' => 'Entrepreneur', 'goal' => 'I want to validate a small business that helps local shops.'])
            ->assertRedirect(route('learning-paths.index'));

        $path = LearningPath::with('items')->firstOrFail();
        $this->assertSame('Entrepreneur', $path->target_role);
        $this->assertCount(3, $path->items);
        $this->assertDatabaseHas('learning_path_items', ['learning_path_id' => $path->id, 'skill_name' => 'Customer discovery', 'status' => 'planned']);

        $this->actingAs($user)
            ->patch(route('learning-path-items.update', $path->items->first()), ['status' => 'completed'])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('learning_path_items', ['id' => $path->items->first()->id, 'status' => 'completed']);
    }

    public function test_user_cannot_update_another_users_learning_path_item(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now(), 'onboarding_completed_at' => now()]);
        $other = User::factory()->create(['email_verified_at' => now(), 'onboarding_completed_at' => now()]);
        $item = $owner->learningPaths()->create(['title' => 'Private plan'])->items()->create(['skill_name' => 'Laravel', 'title' => 'Build Laravel', 'position' => 1]);

        $this->actingAs($other)
            ->patch(route('learning-path-items.update', $item), ['status' => 'completed'])
            ->assertNotFound();
    }
}
