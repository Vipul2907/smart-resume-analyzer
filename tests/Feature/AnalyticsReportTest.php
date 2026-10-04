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

    public function test_analytics_shows_new_resume_and_ats_scores_from_saved_analysis_results(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ]);

        $user->aiAnalyses()->create([
            'analysis_type' => 'resume_review',
            'status' => 'completed',
            'score' => 72,
            'result' => ['score' => 72],
            'completed_at' => now()->subMinute(),
        ]);
        $user->aiAnalyses()->create([
            'analysis_type' => 'ats_foundation',
            'status' => 'completed',
            'result' => ['score' => 89],
            'completed_at' => now(),
        ]);

        $this->actingAs($user)->get(route('analytics'))
            ->assertOk()
            ->assertSee('resume review')
            ->assertSee('ats foundation')
            ->assertSee('72/100')
            ->assertSee('89/100')
            ->assertSee('Latest resume score')
            ->assertSee('89/100');
    }
}
