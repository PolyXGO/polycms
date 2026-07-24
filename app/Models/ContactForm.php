<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactForm extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'fields',
        'type',
        'is_active',
    ];

    protected $casts = [
        'fields' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get submissions for this contact form
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(ContactSubmission::class, 'form_id');
    }
}
