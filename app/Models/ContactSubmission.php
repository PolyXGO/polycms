<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactSubmission extends Model
{
    protected $table = 'contacts';

    protected $fillable = [
        'form_id',
        'type',
        'name',
        'email',
        'data',
        'status',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get the form associated with this submission
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(ContactForm::class, 'form_id');
    }
}
