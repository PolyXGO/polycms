<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array<int, string>
     */
    protected $except = [
        'current_password',
        'password',
        'password_confirmation',
        'content_raw',
        'description_blocks',
    ];

    /**
     * Transform the given value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function transform($key, $value)
    {
        // If the key starts with content_raw or description_blocks, do not trim
        if (str_starts_with($key, 'content_raw') || str_starts_with($key, 'description_blocks')) {
            return $value;
        }

        return parent::transform($key, $value);
    }
    
    /**
     * Clean the given value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function cleanValue($key, $value)
    {
        if (is_array($value) && (str_starts_with($key, 'content_raw') || str_starts_with($key, 'description_blocks'))) {
            return $value;
        }

        return parent::cleanValue($key, $value);
    }
}
