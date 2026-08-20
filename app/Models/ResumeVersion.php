<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeVersion extends Model
{
    protected $fillable = [
        'resume_id',
        'version_number',
        'label',
        'change_summary',
        'content',
        'is_current',
        'user_id',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'content' => 'array',
            'is_current' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }
}
