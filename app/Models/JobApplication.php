<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobApplication extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(JobContact::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(JobAttachment::class);
    }

    protected function casts(): array
    {
        return [
            'applied_at' => 'date',
            'application_date' => 'date',
            'follow_up_at' => 'date',
            'deadline' => 'date',
            'priority' => 'integer',
        ];
    }
}
