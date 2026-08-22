<?php

namespace Tests\Feature;

use App\Models\Skill;
use App\Models\InterviewSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkspaceCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_start_an_interview_practice_session_with_questions(): void
    {
        $user = $this->user();

        $this->actingAs($user)->post(route('interviews.store'), [
            'title' => 'Technical practice',
            'session_type' => 'technical',
            'duration_minutes' => 30,
        ])->assertSessionHas('status');

        $this->assertDatabaseHas('interview_sessions', [
            'user_id' => $user->id,
            'title' => 'Technical practice',
            'status' => 'in_progress',
        ]);
        $this->assertDatabaseHas('interview_sessions', ['user_id' => $user->id, 'questions_count' => 5]);
        $session = InterviewSession::firstOrFail();
        $this->actingAs($user)->patch(route('interviews.responses.update', $session), ['answers' => ['I built a reliable feature with clear results.']])->assertSessionHas('status');
        $this->assertSame(1, $session->fresh()->completed_questions);
    }

    public function test_user_can_save_a_skill_with_a_private_certificate(): void
    {
        Storage::fake('local');
        $user = $this->user();

        $this->actingAs($user)->post(route('skills.store'), [
            'name' => 'Python',
            'category' => 'Technical',
            'proficiency' => 100,
            'years_experience' => 5,
            'evidence' => 'Completed production automation work.',
            'certificate' => UploadedFile::fake()->create('python-certificate.pdf', 200, 'application/pdf'),
        ])->assertSessionHas('status');

        $skill = Skill::firstOrFail();
        $this->assertSame(100, $skill->proficiency);
        $this->assertNotEmpty($skill->certificate_path);
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        $disk->assertExists($skill->certificate_path);
        $this->actingAs($user)->get(route('skills.certificate.download', $skill))->assertOk();
    }

    private function user(): User
    {
        return User::factory()->create(['email_verified_at' => now(), 'onboarding_completed_at' => now()]);
    }
}
