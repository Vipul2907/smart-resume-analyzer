<?php

namespace Tests\Feature;

use App\Models\AdminActivity;
use App\Models\SupportRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdminAreaTest extends TestCase
{
    use RefreshDatabase;

    public function test_normal_user_cannot_open_admin_area(): void
    {
        $member = $this->user();

        $this->actingAs($member)->get(route('admin.index'))->assertForbidden();
    }

    public function test_admin_user_list_shows_ten_accounts_per_page_and_searches_email(): void
    {
        $admin = $this->user(['is_admin' => true]);

        foreach (range(1, 12) as $number) {
            $this->user(['email' => "member{$number}@example.test"]);
        }

        $this->actingAs($admin)->get(route('admin.index'))
            ->assertOk()
            ->assertSee('13 total accounts')
            ->assertSee('Page 1/2');

        $this->actingAs($admin)->get(route('admin.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Page 2/2');

        $this->actingAs($admin)->get(route('admin.index', ['q' => 'member7@example.test']))
            ->assertOk()
            ->assertSee('member7@example.test')
            ->assertDontSee('member2@example.test');
    }

    public function test_admin_can_view_private_user_records_and_normal_user_cannot(): void
    {
        $admin = $this->user(['is_admin' => true]);
        $member = $this->user(['name' => 'Private Member', 'email' => 'private@example.test']);
        $member->resumes()->create([
            'name' => 'Senior Developer Resume',
            'original_filename' => 'senior-resume.pdf',
            'file_path' => 'resumes/private/senior-resume.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 2048,
            'extracted_text' => 'Private resume text for authorized support review.',
            'parse_status' => 'parsed',
        ]);
        $member->privateDocuments()->create([
            'name' => 'Training certificate',
            'original_filename' => 'certificate.pdf',
            'file_path' => 'documents/private/certificate.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'category' => 'certificate',
        ]);

        $this->actingAs($admin)->get(route('admin.users.show', $member))
            ->assertOk()
            ->assertSee('Private Member')
            ->assertSee('Senior Developer Resume')
            ->assertSee('Private resume text for authorized support review.')
            ->assertSee('Training certificate')
            ->assertSee('Resume records (1)')
            ->assertSee('Private document vault (1)')
            ->assertSee('older actions were not logged');

        $this->assertDatabaseHas('admin_activities', [
            'admin_user_id' => $admin->id,
            'subject_user_id' => $member->id,
            'route_name' => 'admin.users.show',
            'http_method' => 'GET',
            'response_code' => 200,
        ]);

        $this->actingAs($admin)->get(route('admin.index'))
            ->assertOk()
            ->assertSee('Recent administrator access')
            ->assertSee('private@example.test');

        $this->actingAs($member)->get(route('admin.users.show', $member))->assertForbidden();
    }

    public function test_verified_account_page_visits_are_recorded_without_form_contents(): void
    {
        $member = $this->user();

        $this->actingAs($member)->get(route('dashboard'))->assertOk();

        $this->assertDatabaseHas('user_activities', [
            'user_id' => $member->id,
            'route_name' => 'dashboard',
            'http_method' => 'GET',
            'response_code' => 200,
            'route_parameters' => null,
        ]);
    }

    public function test_admin_can_manage_support_and_publish_announcements(): void
    {
        $admin = $this->user(['is_admin' => true]);
        $member = $this->user();
        $ticket = SupportRequest::create([
            'user_id' => $member->id,
            'subject' => 'Need resume help',
            'category' => 'resume',
            'message' => 'I would like to understand the ATS recommendations in my report.',
            'status' => 'open',
        ]);

        $this->actingAs($admin)->get(route('admin.index'))
            ->assertOk()
            ->assertSee('Platform control centre.')
            ->assertSee('Need resume help');

        $this->actingAs($admin)->patch(route('admin.tickets.update', $ticket), [
            'status' => 'resolved',
            'admin_response' => 'Your ATS recommendations are now available in the resume analysis screen.',
        ])->assertSessionHas('status');

        $this->assertDatabaseHas('support_requests', ['id' => $ticket->id, 'status' => 'resolved']);
        $this->assertDatabaseHas('admin_activities', [
            'admin_user_id' => $admin->id,
            'subject_user_id' => $member->id,
            'route_name' => 'admin.tickets.update',
            'http_method' => 'PATCH',
        ]);

        $this->actingAs($admin)->post(route('admin.announcements.store'), [
            'title' => 'New interview guide',
            'body' => 'The interview lab guide is now available in the help centre.',
        ])->assertSessionHas('status');

        $this->assertDatabaseHas('admin_announcements', ['title' => 'New interview guide']);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $member->id, 'type' => 'platform_announcement']);
    }

    public function test_first_admin_command_requires_verified_account_and_refuses_to_replace_an_existing_admin(): void
    {
        $unverified = User::factory()->create(['email' => 'unverified@example.test', 'email_verified_at' => null]);
        $this->assertSame(1, Artisan::call('smartcv:grant-admin', ['email' => $unverified->email, '--force' => true]));
        $this->assertFalse($unverified->fresh()->is_admin);

        $firstAdmin = $this->user(['email' => 'admin@example.test']);
        $this->assertSame(0, Artisan::call('smartcv:grant-admin', ['email' => $firstAdmin->email, '--force' => true]));
        $this->assertTrue($firstAdmin->fresh()->is_admin);

        $second = $this->user(['email' => 'second@example.test']);
        $this->assertSame(1, Artisan::call('smartcv:grant-admin', ['email' => $second->email, '--force' => true]));
        $this->assertFalse($second->fresh()->is_admin);
    }

    public function test_activity_cleanup_removes_only_records_older_than_ninety_days(): void
    {
        $user = $this->user();
        $oldActivity = $user->activities()->create([
            'route_name' => 'dashboard', 'http_method' => 'GET', 'response_code' => 200,
            'created_at' => now()->subDays(91),
        ]);
        $recentActivity = $user->activities()->create([
            'route_name' => 'jobs', 'http_method' => 'GET', 'response_code' => 200,
            'created_at' => now()->subDays(2),
        ]);
        $oldAdminActivity = AdminActivity::query()->create([
            'admin_user_id' => $user->id,
            'route_name' => 'admin.index',
            'http_method' => 'GET',
            'response_code' => 200,
            'created_at' => now()->subDays(91),
        ]);
        $recentAdminActivity = AdminActivity::query()->create([
            'admin_user_id' => $user->id,
            'route_name' => 'admin.users.show',
            'http_method' => 'GET',
            'response_code' => 200,
            'created_at' => now()->subDays(2),
        ]);

        Artisan::call('smartcv:prune-activity');

        $this->assertDatabaseMissing('user_activities', ['id' => $oldActivity->id]);
        $this->assertDatabaseHas('user_activities', ['id' => $recentActivity->id]);
        $this->assertDatabaseMissing('admin_activities', ['id' => $oldAdminActivity->id]);
        $this->assertDatabaseHas('admin_activities', ['id' => $recentAdminActivity->id]);
    }

    /** @param array<string, mixed> $attributes */
    private function user(array $attributes = []): User
    {
        return User::factory()->create($attributes + [
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ]);
    }
}
