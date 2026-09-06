<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'email_reminders' => 'boolean',
            'weekly_career_review' => 'boolean',
            'in_app_reminders' => 'boolean',
            'ai_processing_enabled' => 'boolean',
            'retain_ai_history' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
