<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_the_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_a_new_user_can_register_for_a_free_account(): void
    {
        $response = $this->post('/register', [
            'name' => 'Noah',
            'email' => 'noah@example.test',
            'password' => 'career123',
            'password_confirmation' => 'career123',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertDatabaseHas('users', ['email' => 'noah@example.test']);
        $this->assertAuthenticated();
    }

    public function test_registration_cannot_assign_administrator_access(): void
    {
        $this->post('/register', [
            'name' => 'Untrusted request',
            'email' => 'member@example.test',
            'password' => 'career123',
            'password_confirmation' => 'career123',
            'is_admin' => '1',
        ])->assertRedirect(route('verification.notice'));

        $this->assertDatabaseHas('users', ['email' => 'member@example.test', 'is_admin' => false]);
    }

    public function test_a_verified_user_can_complete_onboarding(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->post('/onboarding', [
            'target_role' => 'Product Designer',
            'experience_level' => 'mid',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'target_role' => 'Product Designer',
            'experience_level' => 'mid',
        ]);
    }

    public function test_a_valid_password_reset_token_changes_the_password_and_rotates_remember_token(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);
        $rememberToken = $user->remember_token;
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect(route('login'))->assertSessionHas('status');

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
        $this->assertNotSame($rememberToken, $user->fresh()->remember_token);
    }

    public function test_an_invalid_password_reset_token_never_reports_success_or_changes_password(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);

        $this->from(route('password.reset', ['token' => 'invalid-token']))
            ->post(route('password.update'), [
                'token' => 'invalid-token',
                'email' => $user->email,
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect(route('password.reset', ['token' => 'invalid-token']))
            ->assertSessionHasErrors('email')
            ->assertSessionMissing('status');

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }
}
