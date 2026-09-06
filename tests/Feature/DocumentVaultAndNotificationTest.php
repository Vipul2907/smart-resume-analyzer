<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentVaultAndNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_store_preview_download_and_remove_a_private_document(): void
    {
        Storage::fake('local');
        $user = $this->user();

        $this->actingAs($user)->post(route('documents.store'), [
            'name' => 'Interview notes',
            'document' => UploadedFile::fake()->createWithContent('notes.txt', 'Prepare STAR examples for the product interview.'),
        ])->assertSessionHas('status');

        $document = $user->privateDocuments()->firstOrFail();
        $this->assertSame('Prepare STAR examples for the product interview.', $document->extracted_text);
        Storage::disk('local')->assertExists($document->file_path);
        $this->actingAs($user)->get(route('documents.download', $document))->assertOk();
        $this->actingAs($user)->delete(route('documents.destroy', $document))->assertSessionHas('status');
        Storage::disk('local')->assertMissing($document->file_path);
    }

    public function test_job_follow_up_creates_a_private_in_app_reminder(): void
    {
        $user = $this->user();
        JobApplication::query()->create([
            'user_id' => $user->id,
            'company' => 'Smart Company',
            'role' => 'Laravel Developer',
            'status' => 'applied',
            'follow_up_at' => today()->addDays(2),
        ]);

        $this->actingAs($user)->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Follow up with Smart Company');

        $notification = $user->notifications()->firstOrFail();
        $this->actingAs($user)->patch(route('notifications.read', $notification->id))->assertSessionHas('status');
        $this->assertNotNull($notification->fresh()->read_at);
    }

    private function user(): User
    {
        return User::factory()->create(['email_verified_at' => now(), 'onboarding_completed_at' => now()]);
    }
}
