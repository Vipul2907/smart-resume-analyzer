<?php

namespace Tests\Feature;

use App\Models\JobSearch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobDiscoveryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_save_a_search_and_add_discovered_job_to_tracker(): void
    {
        $user = $this->user();
        $this->actingAs($user)->post(route('discover.searches.store'), [
            'name' => 'Remote founder roles', 'keywords' => 'startup founder', 'location' => 'India',
            'work_mode' => 'remote', 'experience_level' => 'leadership', 'frequency' => 'weekly', 'is_alert_enabled' => '1',
        ])->assertRedirect(route('discover', ['q' => 'startup founder']));

        $search = $user->jobSearches()->firstOrFail();
        $this->assertTrue($search->is_alert_enabled);
        $this->actingAs($user)->post(route('discover.tracker.store'), [
            'company' => 'Example Ventures', 'role' => 'Founder', 'location' => 'Remote', 'job_url' => 'https://example.test/jobs/founder',
        ])->assertRedirect(route('jobs'));
        $this->assertDatabaseHas('job_applications', ['user_id' => $user->id, 'company' => 'Example Ventures', 'status' => 'saved']);
    }

    public function test_user_cannot_manage_another_users_saved_search(): void
    {
        $owner = $this->user();
        $other = $this->user();
        $search = $owner->jobSearches()->create(['name' => 'Private', 'keywords' => 'designer', 'frequency' => 'weekly']);

        $this->actingAs($other)->delete(route('discover.searches.destroy', $search))->assertNotFound();
    }

    private function user(): User
    {
        return User::factory()->create(['email_verified_at' => now(), 'onboarding_completed_at' => now()]);
    }
}
