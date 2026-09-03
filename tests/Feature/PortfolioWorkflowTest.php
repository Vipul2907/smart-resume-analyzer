<?php

namespace Tests\Feature;

use App\Models\CareerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_publish_selected_projects_on_a_public_portfolio(): void
    {
        $user = $this->user();

        $this->actingAs($user)->patch(route('portfolio.settings.update'), [
            'public_slug' => 'noah-career',
            'contact_email' => 'noah@example.test',
            'portfolio_is_public' => '1',
            'show_contact_email' => '1',
        ])->assertSessionHas('status');

        $this->actingAs($user)->post(route('portfolio.store'), [
            'title' => 'Local Shop Discovery',
            'tagline' => 'Validated a small-business problem.',
            'role' => 'Founder',
            'description' => 'Interviewed local owners to understand their daily workflow.',
            'outcome' => 'Completed ten customer interviews.',
            'case_study' => 'I spoke with owners, grouped the problems, and tested a simple offer.',
            'skills' => 'Customer discovery, Validation, Sales',
            'visibility' => 'public',
            'is_featured' => '1',
        ])->assertSessionHas('status');

        $this->get(route('portfolio.public', 'noah-career'))
            ->assertOk()
            ->assertSee('Local Shop Discovery')
            ->assertSee('Contact '.$user->name)
            ->assertSee('Customer discovery');
    }

    public function test_private_portfolios_are_not_visible_to_the_public(): void
    {
        $user = $this->user();
        CareerProfile::create(['user_id' => $user->id, 'public_slug' => 'private-noah', 'portfolio_is_public' => false]);

        $this->get(route('portfolio.public', 'private-noah'))->assertNotFound();
    }

    public function test_user_cannot_update_another_users_project(): void
    {
        $owner = $this->user();
        $other = $this->user();
        $project = $owner->portfolioProjects()->create(['title' => 'Private project', 'visibility' => 'private']);

        $this->actingAs($other)->patch(route('portfolio.update', $project), [
            'title' => 'Changed project', 'visibility' => 'private',
        ])->assertNotFound();
    }

    private function user(): User
    {
        return User::factory()->create(['email_verified_at' => now(), 'onboarding_completed_at' => now()]);
    }
}
