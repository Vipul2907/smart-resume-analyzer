<?php

namespace Tests\Feature;

use App\Models\SupportRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAreaTest extends TestCase
{
    use RefreshDatabase;

    public function test_normal_user_cannot_open_admin_area(): void
    {
        $member = $this->user();

        $this->actingAs($member)->get(route('admin.index'))->assertForbidden();
    }

    public function test_admin_can_manage_support_and_publish_announcements(): void
    {
        $admin = $this->user(['is_admin' => true]);
        $member = $this->user();
        $ticket = SupportRequest::create([
            'user_id' => $member->id,
            'subject' => 'Need resume help',
            'category' => 'resume',
            'message' => 'I would like to understand the ATS recommendations in my report.',
            'status' => 'open',
        ]);

        $this->actingAs($admin)->get(route('admin.index'))
            ->assertOk()
            ->assertSee('Platform control centre.')
            ->assertSee('Need resume help');

        $this->actingAs($admin)->patch(route('admin.tickets.update', $ticket), [
            'status' => 'resolved',
            'admin_response' => 'Your ATS recommendations are now available in the resume analysis screen.',
        ])->assertSessionHas('status');

        $this->assertDatabaseHas('support_requests', ['id' => $ticket->id, 'status' => 'resolved']);

        $this->actingAs($admin)->post(route('admin.announcements.store'), [
            'title' => 'New interview guide',
            'body' => 'The interview lab guide is now available in the help centre.',
        ])->assertSessionHas('status');

        $this->assertDatabaseHas('admin_announcements', ['title' => 'New interview guide']);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $member->id, 'type' => 'platform_announcement']);
    }

    /** @param array<string, mixed> $attributes */
    private function user(array $attributes = []): User
    {
        return User::factory()->create($attributes + [
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ]);
    }
}
