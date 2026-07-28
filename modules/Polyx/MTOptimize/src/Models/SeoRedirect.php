<?php

declare(strict_types=1);

namespace Modules\Polyx\MTOptimize\Models;

use Illuminate\Database\Eloquent\Model;

class SeoRedirect extends Model
{
    protected $table = 'seo_redirects';

    protected $fillable = [
        'from_path',
        'to_url',
        'type',
        'is_active',
        'note',
        'hits',
        'last_hit_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => 'integer',
            'is_active' => 'boolean',
            'hits' => 'integer',
            'last_hit_at' => 'datetime',
        ];
    }
}
