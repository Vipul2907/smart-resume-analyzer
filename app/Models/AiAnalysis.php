<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAnalysis extends Model
{
    protected $fillable = [
        'user_id',
        'resume_id',
        'job_application_id',
        'analysis_type',
        'provider',
        'model',
        'status',
        'prompt_version',
        'input_snapshot',
        'result',
        'score',
        'input_tokens',
        'output_tokens',
        'error_message',
        'requested_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'input_snapshot' => 'array',
            'result' => 'array',
            'score' => 'integer',
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
            'requested_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }
}
