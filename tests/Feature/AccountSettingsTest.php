<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_save_privacy_and_reminder_preferences(): void
    {
        $user = $this->user();

        $this->actingAs($user)->get(route('settings'))
            ->assertOk()
            ->assertSee('Control your SmartCV account.');

        $this->actingAs($user)->patch(route('settings.preferences.update'), [
            'email_reminders' => '1',
            'weekly_career_review' => '1',
            'in_app_reminders' => '0',
            'ai_processing_enabled' => '0',
            'retain_ai_history' => '1',
        ])->assertSessionHas('status');

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
            'in_app_reminders' => 0,
            'ai_processing_enabled' => 0,
        ]);
    }

    public function test_user_can_change_password_with_current_password(): void
    {
        $currentPassword = 'CurrentPassword123!';
        $user = $this->user($currentPassword);

        $this->actingAs($user)->patch(route('settings.password.update'), [
            'current_password' => $currentPassword,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])->assertSessionHas('status');

        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
    }

    public function test_user_must_confirm_before_deleting_account(): void
    {
        $currentPassword = 'CurrentPassword123!';
        $user = $this->user($currentPassword);

        $this->actingAs($user)->delete(route('settings.destroy'), [
            'current_password' => $currentPassword,
            'confirmation' => 'DELETE',
        ])->assertRedirect(route('home'));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    private function user(string $password = 'Password123!'): User
    {
        return User::factory()->create([
            'password' => Hash::make($password),
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ]);
    }
}
