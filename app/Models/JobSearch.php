<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSearch extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_alert_enabled' => 'boolean',
            'last_opened_at' => 'datetime',
            'last_alerted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function queryText(): string
    {
        return trim(implode(' ', array_filter([$this->keywords, $this->location, $this->work_mode])));
    }
}
