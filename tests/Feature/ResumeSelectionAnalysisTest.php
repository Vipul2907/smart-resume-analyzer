<?php

namespace Tests\Feature;

use App\Models\Resume;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ResumeSelectionAnalysisTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_secondary_resume_is_the_actual_ai_input_and_is_labeled_in_the_result(): void
    {
        config(['services.groq.key' => 'test-key']);
        $payload = [];
        Http::fake(function ($request) use (&$payload) {
            $payload = $request->data();
            $result = [
                'score' => 82, 'strengths' => ['Selected resume strength'], 'weaknesses' => [], 'missing_sections' => [], 'next_actions' => [],
            ];

            return Http::response(['choices' => [['message' => ['content' => json_encode($result)]]]]);
        });

        $user = $this->user();
        $primary = $this->resume($user, 'Primary Resume', 'PRIMARY-ONLY-CONTENT', true);
        $secondary = $this->resume($user, 'Software Engineer 2026', 'SECONDARY-ONLY-CONTENT', false);

        $this->actingAs($user)->post(route('ai-analyses.store', $primary), [
            'resume_id' => $secondary->id,
            'analysis_type' => 'resume_review',
            'accepted_ai_privacy' => '1',
        ])->assertRedirect(route('analyze', ['resume' => $secondary->id]));

        $this->assertDatabaseHas('ai_analyses', ['resume_id' => $secondary->id, 'status' => 'completed']);
        $this->assertStringContainsString('SECONDARY-ONLY-CONTENT', $payload['messages'][1]['content']);
        $this->assertStringNotContainsString('PRIMARY-ONLY-CONTENT', $payload['messages'][1]['content']);

        $this->actingAs($user)->get(route('analyze', ['resume' => $secondary->id]))
            ->assertOk()
            ->assertSee('Analyzed resume:')
            ->assertSee('Software Engineer 2026');
    }

    public function test_primary_resume_is_used_as_the_default_when_no_resume_id_is_submitted(): void
    {
        config(['services.groq.key' => 'test-key']);
        $result = ['score' => 75, 'strengths' => [], 'weaknesses' => [], 'missing_sections' => [], 'next_actions' => []];
        Http::fake(['api.groq.com/*' => Http::response(['choices' => [['message' => ['content' => json_encode($result)]]]])]);

        $user = $this->user();
        $primary = $this->resume($user, 'Primary Resume', 'PRIMARY-ONLY-CONTENT', true);
        $this->resume($user, 'Secondary Resume', 'SECONDARY-ONLY-CONTENT', false);

        $this->actingAs($user)->post(route('ai-analyses.store', $primary), [
            'analysis_type' => 'resume_review', 'accepted_ai_privacy' => '1',
        ])->assertRedirect(route('analyze', ['resume' => $primary->id]));

        $this->assertDatabaseHas('ai_analyses', ['resume_id' => $primary->id, 'status' => 'completed']);
    }

    public function test_another_users_selected_resume_is_rejected_instead_of_falling_back_to_primary(): void
    {
        $user = $this->user();
        $owner = $this->user();
        $primary = $this->resume($user, 'Primary Resume', 'PRIMARY-ONLY-CONTENT', true);
        $foreign = $this->resume($owner, 'Private Foreign Resume', 'FOREIGN-CONTENT', true);

        $this->actingAs($user)->post(route('ai-analyses.store', $primary), [
            'resume_id' => $foreign->id,
            'analysis_type' => 'resume_review',
            'accepted_ai_privacy' => '1',
        ])->assertNotFound();

        $this->actingAs($user)->get(route('analyze', ['resume' => $foreign->id]))->assertNotFound();
        $this->assertDatabaseMissing('ai_analyses', ['resume_id' => $foreign->id]);
    }

    private function user(): User
    {
        return User::factory()->create(['email_verified_at' => now(), 'onboarding_completed_at' => now()]);
    }

    private function resume(User $user, string $name, string $text, bool $primary): Resume
    {
        return $user->resumes()->create([
            'name' => $name, 'original_filename' => str($name)->slug().'.txt', 'file_path' => 'resumes/'.$user->id.'/'.str($name)->slug().'.txt',
            'mime_type' => 'text/plain', 'file_size' => strlen($text), 'extracted_text' => $text, 'parse_status' => 'parsed', 'is_primary' => $primary,
        ]);
    }
}
