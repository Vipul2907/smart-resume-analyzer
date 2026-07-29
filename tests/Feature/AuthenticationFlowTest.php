<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
