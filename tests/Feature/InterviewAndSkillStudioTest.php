<?php

namespace Tests\Feature;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InterviewAndSkillStudioTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_leadership_interview_and_save_private_coaching_feedback(): void
    {
        config(['services.groq.key' => 'test-key']);
        $feedback = ['score' => 81, 'strengths' => ['Uses a real example.'], 'improvements' => ['Add a clearer stakeholder outcome.']];
        $responsePayload = [
            'choices' => [[
                'message' => ['content' => json_encode($feedback)],
            ]],
        ];
        Http::fake(['api.groq.com/*' => Http::response($responsePayload)]);
        $user = $this->user();

        $this->actingAs($user)->post(route('interviews.store'), [
            'title' => 'Leadership round', 'target_role' => 'Engineering Manager',
            'session_type' => 'leadership', 'duration_minutes' => 45,
        ])->assertSessionHas('status');

        $interview = $user->interviewSessions()->firstOrFail();
        $this->assertCount(5, $interview->questions);

        $this->actingAs($user)->patch(route('interviews.responses.update', $interview), [
            'answers' => ['When I led a project, I improved delivery time by 20%.'],
        ])->assertSessionHas('status');
        $this->actingAs($user)->patch(route('interviews.complete', $interview))->assertSessionHas('status');

        $this->assertDatabaseHas('interview_sessions', ['id' => $interview->id, 'status' => 'completed']);
        $this->assertNotEmpty($interview->fresh()->feedback);
    }

    public function test_gibberish_interview_answers_are_not_scored(): void
    {
        $user = $this->user();
        $interview = $user->interviewSessions()->create(['title' => 'Practice', 'status' => 'in_progress', 'questions' => ['Tell me about a project.'], 'responses' => ['dbhbvsuas']]);

        $this->actingAs($user)->patch(route('interviews.complete', $interview))
            ->assertSessionHasErrors('answers');

        $this->assertDatabaseHas('interview_sessions', ['id' => $interview->id, 'status' => 'in_progress']);
    }

    public function test_user_can_update_a_skill_and_complete_a_milestone(): void
    {
        $user = $this->user();
        $skill = $user->skills()->create(['name' => 'Laravel', 'proficiency' => 45]);

        $this->actingAs($user)->patch(route('skills.update', $skill), [
            'proficiency' => 60, 'target_proficiency' => 85, 'is_priority' => '1', 'evidence' => 'Built a private API.',
        ])->assertSessionHas('status');
        $this->actingAs($user)->post(route('skills.milestones.store', $skill), ['title' => 'Ship a tested Laravel feature'])->assertSessionHas('status');

        $milestone = $skill->milestones()->firstOrFail();
        $this->actingAs($user)->patch(route('skills.milestones.update', [$skill, $milestone]), ['status' => 'completed'])->assertSessionHas('status');

        $this->assertDatabaseHas('skills', ['id' => $skill->id, 'proficiency' => 60, 'target_proficiency' => 85]);
        $this->assertDatabaseHas('skill_milestones', ['id' => $milestone->id, 'status' => 'completed']);
    }

    private function user(): User
    {
        return User::factory()->create(['email_verified_at' => now(), 'onboarding_completed_at' => now()]);
    }
}
