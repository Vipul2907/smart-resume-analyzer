<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminActivity extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'admin_user_id',
        'subject_user_id',
        'route_name',
        'http_method',
        'route_parameters',
        'response_code',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'route_parameters' => 'array',
            'response_code' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subject_user_id');
    }
}
