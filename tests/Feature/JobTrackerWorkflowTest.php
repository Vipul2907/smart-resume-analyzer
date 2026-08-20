<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JobTrackerWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_filter_and_edit_a_job_application(): void
    {
        $user = $this->user();

        $this->actingAs($user)->post(route('jobs.store'), [
            'company' => 'Acme Labs', 'role' => 'Product Designer', 'status' => 'saved',
            'location' => 'Remote', 'work_mode' => 'Remote', 'priority' => 2,
            'follow_up_at' => now()->addWeek()->toDateString(), 'notes' => 'Portfolio requested.',
        ])->assertSessionHas('status');

        $job = $user->jobApplications()->firstOrFail();
        $this->assertDatabaseHas('job_applications', ['id' => $job->id, 'company' => 'Acme Labs', 'priority' => 2]);
        $this->actingAs($user)->get(route('jobs', ['status' => 'saved', 'search' => 'Acme']))->assertOk()->assertSee('Acme Labs');

        $this->actingAs($user)->patch(route('jobs.update', $job), [
            'company' => 'Acme Labs', 'role' => 'Senior Product Designer', 'status' => 'interviewing',
            'location' => 'Remote', 'work_mode' => 'Remote', 'priority' => 3, 'notes' => 'First interview booked.',
        ])->assertSessionHas('status');

        $this->assertDatabaseHas('job_applications', ['id' => $job->id, 'role' => 'Senior Product Designer', 'status' => 'interviewing']);
    }

    public function test_user_can_manage_private_contacts_and_attachments(): void
    {
        Storage::fake('local');
        $user = $this->user();
        $job = $user->jobApplications()->create(['company' => 'Northstar', 'role' => 'Engineer', 'status' => 'applied']);

        $this->actingAs($user)->post(route('jobs.contacts.store', $job), ['name' => 'Jordan Lee', 'role' => 'Recruiter', 'email' => 'jordan@example.test'])->assertSessionHas('status');
        $contact = $job->contacts()->firstOrFail();

        $this->actingAs($user)->post(route('jobs.attachments.store', $job), ['attachment' => UploadedFile::fake()->create('job-notes.pdf', 200, 'application/pdf')])->assertSessionHas('status');
        $attachment = $job->attachments()->firstOrFail();
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        $disk->assertExists($attachment->file_path);

        $other = $this->user();
        $this->actingAs($other)->get(route('jobs.attachments.download', [$job, $attachment]))->assertNotFound();
        $this->actingAs($user)->delete(route('jobs.contacts.destroy', [$job, $contact]))->assertSessionHas('status');
        $this->actingAs($user)->delete(route('jobs.attachments.destroy', [$job, $attachment]))->assertSessionHas('status');
        $disk->assertMissing($attachment->file_path);
    }

    private function user(): User
    {
        return User::factory()->create(['email_verified_at' => now(), 'onboarding_completed_at' => now()]);
    }
}
