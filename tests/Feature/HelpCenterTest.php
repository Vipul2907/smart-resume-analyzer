<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_search_guides_and_submit_private_support_request(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ]);

        $this->actingAs($user)->get(route('help', ['q' => 'resume']))
            ->assertOk()
            ->assertSee('Build your first resume')
            ->assertDontSee('Track every opportunity');

        $this->actingAs($user)->post(route('help.requests.store'), [
            'subject' => 'I need help with my resume',
            'category' => 'resume',
            'message' => 'My resume upload completed, but I need help understanding the ATS suggestions.',
        ])->assertSessionHas('status');

        $this->assertDatabaseHas('support_requests', [
            'user_id' => $user->id,
            'subject' => 'I need help with my resume',
            'status' => 'open',
        ]);
    }
}
