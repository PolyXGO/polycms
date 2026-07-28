<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = [
        'code',
        'lang',
        'name',
        'native_name',
        'flag',
        'is_default',
        'is_active',
        'direction',
        'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        $clearCache = function () {
            cache()->forget('active_languages_non_default');
            cache()->forget('default_language_code');
        };

        static::saved($clearCache);
        static::deleted($clearCache);
    }
}
