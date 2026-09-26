<?php

namespace Tests\Feature;

use App\Models\CoverLetter;
use App\Models\JobApplication;
use App\Models\Resume;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoverLetterWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_edit_and_duplicate_a_private_cover_letter(): void
    {
        $user = $this->user();
        $job = $user->jobApplications()->create(['company' => 'Acme', 'role' => 'Product Designer', 'status' => 'applied']);
        $resume = $user->resumes()->create([
            'name' => 'Product Resume', 'original_filename' => 'product.txt', 'file_path' => 'resumes/product.txt',
            'mime_type' => 'text/plain', 'file_size' => 100, 'parse_status' => 'parsed',
        ]);

        $this->actingAs($user)->post(route('cover-letters.store'), $this->payload($job, $resume))->assertRedirect();
        $letter = CoverLetter::firstOrFail();

        $this->assertDatabaseHas('cover_letters', [
            'id' => $letter->id, 'user_id' => $user->id, 'job_application_id' => $job->id,
            'resume_id' => $resume->id, 'company_name' => 'Acme', 'status' => 'draft',
        ]);

        $this->actingAs($user)->patch(route('cover-letters.update', $letter), $this->payload($job, $resume, 'ready'))->assertSessionHas('status');
        $this->assertSame('ready', $letter->fresh()->status);

        $this->actingAs($user)->post(route('cover-letters.duplicate', $letter))->assertRedirect();
        $this->assertSame(2, CoverLetter::count());
    }

    public function test_users_cannot_access_each_others_cover_letters(): void
    {
        $owner = $this->user();
        $other = $this->user();
        $letter = $owner->coverLetters()->create(['title' => 'Private letter', 'template' => 'modern', 'body' => 'Private content.', 'status' => 'draft']);

        $this->actingAs($other)->get(route('cover-letters.edit', $letter))->assertNotFound();
        $this->actingAs($other)->get(route('cover-letters.download.txt', $letter))->assertNotFound();
    }

    public function test_cover_letter_preview_uses_a4_print_rules_and_retains_long_body_content(): void
    {
        $user = $this->user();
        $letter = $user->coverLetters()->create([
            'title' => 'Long letter', 'template' => 'classic', 'recipient_name' => 'Hiring Manager',
            'company_name' => 'Acme', 'subject' => 'Application', 'body' => str_repeat('This paragraph explains a relevant project outcome in detail. ', 600),
            'status' => 'ready',
        ]);

        $this->actingAs($user)->get(route('cover-letters.preview', $letter))
            ->assertOk()
            ->assertSee('@page { size: A4; margin: 0; }', false)
            ->assertSee('height: 297mm', false)
            ->assertSee('data-paginated-document', false)
            ->assertSee('splitTextBlock', false)
            ->assertSee('This paragraph explains a relevant project outcome in detail.');
    }

    private function payload(JobApplication $job, Resume $resume, string $status = 'draft'): array
    {
        return [
            'title' => 'Product Designer — Acme', 'template' => 'modern', 'job_application_id' => $job->id,
            'resume_id' => $resume->id, 'recipient_name' => 'Hiring Manager', 'company_name' => 'Acme',
            'job_title' => 'Product Designer', 'subject' => 'Application for Product Designer',
            'opening' => 'Dear Hiring Manager,', 'body' => 'I am excited to apply because I create useful products and can show measurable impact.',
            'closing' => 'Thank you for your consideration.', 'signature_name' => 'Noah', 'status' => $status,
        ];
    }

    private function user(): User
    {
        return User::factory()->create(['email_verified_at' => now(), 'onboarding_completed_at' => now()]);
    }
}
