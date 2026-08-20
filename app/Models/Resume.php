<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resume extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'original_filename',
        'file_path',
        'file_disk',
        'mime_type',
        'file_size',
        'extracted_text',
        'parse_status',
        'is_primary',
        'last_analyzed_at',
        'title',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'is_primary' => 'boolean',
            'is_default' => 'boolean',
            'last_analyzed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ResumeVersion::class);
    }

    public function aiAnalyses(): HasMany
    {
        return $this->hasMany(AiAnalysis::class);
    }

    public function currentVersion(): ?ResumeVersion
    {
        return $this->versions()->where('is_current', true)->latest()->first();
    }

    public function extensionLabel(): string
    {
        return strtoupper(pathinfo($this->original_filename, PATHINFO_EXTENSION) ?: 'FILE');
    }
}
