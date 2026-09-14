<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Creates in-app notifications for both current Laravel notification tables
 * and older SmartCV installations that still require a user_id column.
 */
class UserNotificationService
{
    /** @param array<string, mixed> $data */
    public function create(User $user, string $type, array $data): void
    {
        $attributes = [
            'id' => (string) Str::uuid(),
            'type' => $type,
            'data' => $data,
        ];

        if (Schema::hasColumn('notifications', 'user_id')) {
            $attributes['user_id'] = $user->id;
        }

        $user->notifications()->create($attributes);
    }
}
