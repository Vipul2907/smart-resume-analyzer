<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_private_analytics_and_download_exports(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ]);

        JobApplication::query()->create([
            'user_id' => $user->id,
            'company' => 'Northstar Labs',
            'role' => 'Product Designer',
            'status' => 'interviewing',
            'source' => 'Live Job Board',
        ]);

        $this->actingAs($user)->get(route('analytics'))
            ->assertOk()
            ->assertSee('Your career, measured clearly.')
            ->assertSee('Live Job Board');

        $this->actingAs($user)->get(route('analytics.applications.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $this->actingAs($user)->get(route('analytics.data.export'))
            ->assertOk()
            ->assertHeader('content-type', 'application/json; charset=UTF-8');

        $this->actingAs($user)->get(route('analytics.print'))->assertOk();
        $this->actingAs($user)->get(route('analytics.recruiter-report'))->assertOk();
    }
}
