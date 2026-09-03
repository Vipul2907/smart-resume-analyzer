<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerProfile extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'available_for_work' => 'boolean',
            'portfolio_is_public' => 'boolean',
            'show_contact_email' => 'boolean',
            'show_resume' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
