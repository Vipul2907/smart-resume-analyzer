<?php

namespace Tests\Feature;

use App\Models\Resume;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResumeBuilderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_save_and_duplicate_a_structured_resume(): void
    {
        Storage::fake('local');
        $user = $this->user();
        $payload = $this->payload();

        $this->actingAs($user)->post(route('resumes.builder.store'), $payload)->assertRedirect();
        $resume = Resume::firstOrFail();

        $this->assertSame('ready', $resume->parse_status);
        $this->assertSame('Noah Career Resume', $resume->name);
        $this->assertDatabaseHas('resume_versions', ['resume_id' => $resume->id, 'is_current' => true]);
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        $disk->assertExists($resume->file_path);

        $payload['summary'] = 'Updated summary with measurable impact.';
        $this->actingAs($user)->patch(route('resumes.builder.update', $resume), $payload)->assertSessionHas('status');
        $this->assertSame('Updated summary with measurable impact.', $resume->fresh()->currentVersion()->content['summary']);

        $this->actingAs($user)->post(route('resumes.duplicate', $resume))->assertRedirect();
        $this->assertSame(2, Resume::count());
    }

    public function test_resume_builder_preview_is_private(): void
    {
        $owner = $this->user();
        $other = $this->user();
        $resume = $owner->resumes()->create(['name' => 'Private draft', 'original_filename' => 'draft.json', 'file_path' => 'resumes/a/draft.json', 'mime_type' => 'application/json', 'file_size' => 2]);

        $this->actingAs($other)->get(route('resumes.preview', $resume))->assertNotFound();
        $this->actingAs($other)->get(route('resumes.builder.edit', $resume))->assertNotFound();
    }

    private function payload(): array
    {
        return ['name' => 'Noah Career Resume', 'version_label' => 'Product role', 'template' => 'modern', 'accent_color' => '#7c3aed', 'font_family' => 'Inter', 'page_length' => 'one', 'personal' => ['name' => 'Noah', 'email' => 'noah@example.test', 'phone' => '', 'location' => '', 'website' => '', 'linkedin' => ''], 'summary' => 'Product-minded engineer.', 'experience' => [['title' => 'Engineer', 'company' => 'SmartCV', 'location' => '', 'start' => '2025', 'end' => 'Present', 'highlights_text' => "Built useful features\nImproved onboarding"]], 'education' => [], 'skills' => 'Laravel, MySQL', 'projects' => [], 'certifications' => [], 'awards' => [], 'languages' => [], 'interests' => '', 'custom_sections' => []];
    }

    private function user(): User
    {
        return User::factory()->create(['email_verified_at' => now(), 'onboarding_completed_at' => now()]);
    }
}
