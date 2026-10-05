<?php

use App\Models\AdminActivity;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('smartcv:grant-admin {email : Email address of the trusted team member} {--force : Skip the confirmation prompt}', function (string $email): int {
    if (User::query()->where('is_admin', true)->exists()) {
        $this->error('An administrator already exists. Ask an existing administrator to grant access.');

        return self::FAILURE;
    }

    $user = User::query()->where('email', $email)->first();

    if (! $user) {
        $this->error('No SmartCV user exists with that email address.');

        return self::FAILURE;
    }

    if (! $user->hasVerifiedEmail()) {
        $this->error('This account must verify its email before receiving administrator access.');

        return self::FAILURE;
    }

    if (! $this->option('force') && ! $this->confirm("Grant the first administrator role to {$user->email}?")) {
        $this->comment('No changes were made.');

        return self::SUCCESS;
    }

    $user->forceFill(['is_admin' => true])->save();
    $this->info("Administrator access granted to {$user->email}.");

    return self::SUCCESS;
})->purpose('Grant the first SmartCV administrator role to a verified account');

Artisan::command('smartcv:prune-activity', function (): void {
    $cutoff = now()->subDays(90);
    $userActivities = UserActivity::query()->where('created_at', '<', $cutoff)->delete();
    $adminActivities = AdminActivity::query()->where('created_at', '<', $cutoff)->delete();

    $this->info("Removed {$userActivities} user activity and {$adminActivities} admin audit records older than 90 days.");
})->purpose('Delete user and administrator activity records older than 90 days');

Schedule::command('smartcv:prune-activity')->daily();
