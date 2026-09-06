<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivateDocument extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['file_size' => 'integer'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function extensionLabel(): string
    {
        return strtoupper(pathinfo($this->original_filename, PATHINFO_EXTENSION) ?: 'FILE');
    }
}
