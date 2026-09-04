<?php

namespace Tests\Feature;

use App\Models\AiAnalysis;
use App\Models\Resume;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LiveJobBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_displays_live_technology_jobs_and_uses_clean_text_for_matching(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'onboarding_completed_at' => now()]);
        $resume = Resume::query()->create([
            'user_id' => $user->id,
            'name' => 'Noah Resume',
            'original_filename' => 'noah.pdf',
            'file_path' => 'resumes/noah.pdf',
            'file_disk' => 'local',
            'mime_type' => 'application/pdf',
            'file_size' => 512,
            'extracted_text' => str_repeat('Experienced Laravel developer. ', 10),
            'parse_status' => 'parsed',
            'is_primary' => true,
        ]);

        Http::fake([
            'https://www.arbeitnow.com/api/job-board-api*' => Http::response([
                'data' => [[
                    'title' => 'Laravel Developer',
                    'company_name' => 'Smart Company',
                    'location' => 'Remote',
                    'remote' => true,
                    'description' => '<p>Build secure <strong>Laravel</strong> applications with PHP, MySQL, queues, tests, and APIs for our growing product team.</p>',
                    'tags' => ['PHP', 'Laravel', 'MySQL'],
                    'url' => 'https://example.test/jobs/laravel-developer',
                    'created_at' => now()->timestamp,
                ]],
                'meta' => ['next_page' => null],
            ]),
        ]);

        $response = $this->actingAs($user)->get(route('discover', ['resume' => $resume->id]));

        $response->assertOk()
            ->assertSee('Laravel Developer')
            ->assertSee('Smart Company')
            ->assertSee('PHP')
            ->assertSee('Analyze my resume for this role')
            ->assertSee('Build secure Laravel applications')
            ->assertDontSee('<strong>Laravel</strong>', false);
    }

    public function test_job_match_page_displays_nested_ai_suggestions_without_crashing(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'onboarding_completed_at' => now()]);
        $resume = Resume::query()->create([
            'user_id' => $user->id, 'name' => 'Noah Resume', 'original_filename' => 'noah.pdf',
            'file_path' => 'resumes/noah.pdf', 'file_disk' => 'local', 'mime_type' => 'application/pdf',
            'file_size' => 512, 'extracted_text' => str_repeat('Laravel developer experience. ', 10),
            'parse_status' => 'parsed', 'is_primary' => true,
        ]);
        AiAnalysis::query()->create([
            'user_id' => $user->id, 'resume_id' => $resume->id, 'analysis_type' => 'job_match',
            'status' => 'completed', 'score' => 81, 'completed_at' => now(),
            'result' => ['summary' => ['Strong PHP experience'], 'matching_skills' => [['Laravel', 'MySQL']], 'next_actions' => ['Add one metric']],
        ]);

        $this->actingAs($user)->get(route('match', ['resume' => $resume->id]))
            ->assertOk()
            ->assertSee('Strong PHP experience')
            ->assertSee('Laravel MySQL');
    }
}
