<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_smartcv_workspace_tables_are_available(): void
    {
        foreach ([
            'career_profiles', 'resumes', 'resume_versions', 'job_applications',
            'interview_sessions', 'skills', 'career_goals', 'portfolio_projects',
            'ai_analyses', 'notifications',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "The {$table} table should exist.");
        }
    }

    public function test_workspace_records_belong_to_their_user(): void
    {
        $user = User::factory()->create();

        $profile = $user->careerProfile()->create(['headline' => 'Product designer']);
        $resume = $user->resumes()->create([
            'name' => 'Product Designer Resume',
            'original_filename' => 'product-designer.pdf',
            'file_path' => 'resumes/product-designer.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
        ]);
        $application = $user->jobApplications()->create(['company' => 'SmartCV', 'role' => 'Product Designer']);
        $analysis = $user->aiAnalyses()->create(['resume_id' => $resume->id, 'analysis_type' => 'resume_review']);

        $this->assertTrue($user->is($profile->user));
        $this->assertTrue($user->is($resume->user));
        $this->assertTrue($user->is($application->user));
        $this->assertTrue($user->is($analysis->user));
    }
}
