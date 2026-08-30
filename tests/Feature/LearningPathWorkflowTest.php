<?php

namespace Tests\Feature;

use App\Models\AiAnalysis;
use App\Models\LearningPath;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningPathWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_and_complete_a_learning_path_from_real_job_match_gaps(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'onboarding_completed_at' => now(), 'target_role' => 'Backend Engineer']);
        AiAnalysis::create([
            'user_id' => $user->id,
            'analysis_type' => 'job_match',
            'status' => 'completed',
            'result' => ['missing_skills' => ['Docker', 'Testing']],
        ]);

        $this->actingAs($user)
            ->post(route('learning-paths.store'), ['target_role' => 'Backend Engineer'])
            ->assertRedirect(route('learning-paths.index'));

        $path = LearningPath::with('items')->firstOrFail();
        $this->assertSame('Backend Engineer', $path->target_role);
        $this->assertCount(2, $path->items);
        $this->assertDatabaseHas('learning_path_items', ['learning_path_id' => $path->id, 'skill_name' => 'Docker', 'status' => 'planned']);

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
