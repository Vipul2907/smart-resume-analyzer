<?php

namespace Tests\Feature;

use App\Models\Resume;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResumeWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_and_parse_a_text_resume(): void
    {
        Storage::fake('local');
        $user = $this->onboardedUser();

        $response = $this->actingAs($user)->post(route('resumes.store'), [
            'name' => 'Backend Resume',
            'resume_file' => UploadedFile::fake()->createWithContent(
                'resume.txt',
                "Alex Morgan\nalex@example.test\nSummary\nLaravel developer\nSkills\nPHP, Laravel, SQL"
            ),
        ]);

        $resume = Resume::first();

        $response->assertRedirect(route('resumes.show', $resume));
        $this->assertTrue($resume->is_primary);
        $this->assertSame('parsed', $resume->parse_status);
        $this->assertStringContainsString('Laravel developer', $resume->extracted_text);
        $this->assertDatabaseHas('resume_versions', ['resume_id' => $resume->id, 'is_current' => true]);
        Storage::disk('local')->assertExists($resume->file_path);
    }

    public function test_users_cannot_access_each_others_resumes(): void
    {
        Storage::fake('local');
        $owner = $this->onboardedUser();
        $other = $this->onboardedUser();

        $resume = $owner->resumes()->create([
            'name' => 'Private Resume',
            'original_filename' => 'private.txt',
            'file_path' => 'resumes/'.$owner->id.'/private.txt',
            'mime_type' => 'text/plain',
            'file_size' => 100,
        ]);

        $this->actingAs($other)->get(route('resumes.show', $resume))->assertNotFound();
        $this->actingAs($other)->patch(route('resumes.update', $resume), ['name' => 'Changed'])->assertNotFound();
    }

    public function test_user_can_rename_mark_primary_and_delete_resume(): void
    {
        Storage::fake('local');
        $user = $this->onboardedUser();
        Storage::disk('local')->put('resumes/'.$user->id.'/one.txt', 'one');
        Storage::disk('local')->put('resumes/'.$user->id.'/two.txt', 'two');

        $first = $user->resumes()->create([
            'name' => 'First',
            'original_filename' => 'one.txt',
            'file_path' => 'resumes/'.$user->id.'/one.txt',
            'mime_type' => 'text/plain',
            'file_size' => 3,
            'is_primary' => true,
        ]);
        $second = $user->resumes()->create([
            'name' => 'Second',
            'original_filename' => 'two.txt',
            'file_path' => 'resumes/'.$user->id.'/two.txt',
            'mime_type' => 'text/plain',
            'file_size' => 3,
        ]);

        $this->actingAs($user)->patch(route('resumes.update', $first), ['name' => 'Updated'])->assertSessionHas('status');
        $this->actingAs($user)->post(route('resumes.primary', $second))->assertSessionHas('status');
        $this->actingAs($user)->delete(route('resumes.destroy', $second))->assertRedirect(route('resumes'));

        $this->assertDatabaseHas('resumes', ['id' => $first->id, 'name' => 'Updated', 'is_primary' => true]);
        $this->assertSoftDeleted('resumes', ['id' => $second->id]);
        Storage::disk('local')->assertMissing($second->file_path);
    }

    public function test_ai_analysis_requires_consent_and_saves_groq_result(): void
    {
        config(['services.groq.key' => 'test-key']);
        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [[
                    'message' => ['content' => json_encode([
                        'score' => 84,
                        'strengths' => ['Clear Laravel experience'],
                        'weaknesses' => ['Needs metrics'],
                        'missing_sections' => ['Projects'],
                        'next_actions' => ['Add impact numbers'],
                    ])],
                ]],
                'usage' => ['prompt_tokens' => 50, 'completion_tokens' => 40],
            ]),
        ]);

        $user = $this->onboardedUser();
        $resume = $user->resumes()->create([
            'name' => 'AI Resume',
            'original_filename' => 'ai.txt',
            'file_path' => 'resumes/'.$user->id.'/ai.txt',
            'mime_type' => 'text/plain',
            'file_size' => 100,
            'extracted_text' => 'Alex Morgan Laravel developer with SQL experience.',
            'parse_status' => 'parsed',
            'is_primary' => true,
        ]);

        $this->actingAs($user)->post(route('ai-analyses.store', $resume), [
            'analysis_type' => 'resume_review',
            'accepted_ai_privacy' => '1',
        ])->assertSessionHas('status');

        $this->assertDatabaseHas('ai_analyses', [
            'resume_id' => $resume->id,
            'status' => 'completed',
            'score' => 84,
            'provider' => 'groq',
        ]);
    }

    private function onboardedUser(): User
    {
        return User::factory()->create([
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ]);
    }
}
