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
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'is_current' => 'boolean',
        ];
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }
}
