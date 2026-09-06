<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('smartcv:grant-admin {email : Email address of the trusted team member}', function (string $email): void {
    $user = User::query()->where('email', $email)->first();

    if (! $user) {
        $this->error('No SmartCV user exists with that email address.');
        return;
    }

    $user->update(['is_admin' => true]);
    $this->info("Administrator access granted to {$user->email}.");
})->purpose('Grant SmartCV administrator access to a trusted existing user');
