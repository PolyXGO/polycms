<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentVote extends Model
{
    protected $fillable = [
        'voteable_type',
        'voteable_id',
        'type',
        'ip_address',
        'user_id',
        'source',
    ];

    /**
     * Get the voteable model (Post, Page, Product, etc.)
     */
    public function voteable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who voted (nullable for anonymous)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
