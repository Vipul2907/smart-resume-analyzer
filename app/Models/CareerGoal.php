<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerGoal extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'target_date' => 'date',
            'milestones' => 'array',
            'career_advice' => 'array',
            'career_advice_generated_at' => 'datetime',
            'progress' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
