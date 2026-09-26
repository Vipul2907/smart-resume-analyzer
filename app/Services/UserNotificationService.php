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
            'type' => $type,
            'data' => $data,
        ];

        /*
         * Some SmartCV installations have a dedicated notifications.title
         * column that is required.
         */
        if (Schema::hasColumn('notifications', 'title')) {
            $title = isset($data['title']) && trim((string) $data['title']) !== ''
                ? trim((string) $data['title'])
                : Str::headline(str_replace('_', ' ', $type));

            $attributes['title'] = $title;
        }

        /*
         * Older SmartCV installations may still have user_id.
         */
        if (Schema::hasColumn('notifications', 'user_id')) {
            $attributes['user_id'] = $user->id;
        }

        /*
         * Only generate a UUID when the actual database id column
         * is a string/UUID type.
         *
         * If the column is an integer/bigint auto-increment field,
         * leave it alone and let MySQL generate the ID.
         */
        if (Schema::hasColumn('notifications', 'id')) {
            $idType = Schema::getColumnType('notifications', 'id');

            if (in_array($idType, ['string', 'char', 'varchar', 'uuid'], true)) {
                $attributes['id'] = (string) Str::uuid();
            }
        }

        $user->notifications()->create($attributes);
    }
}
