<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewSession extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'questions' => 'array',
            'responses' => 'array',
            'feedback' => 'array',
            'completed_at' => 'datetime',
            'reminder_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
