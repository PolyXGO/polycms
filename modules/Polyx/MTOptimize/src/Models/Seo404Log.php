<?php

declare(strict_types=1);

namespace Modules\Polyx\MTOptimize\Models;

use Illuminate\Database\Eloquent\Model;

class Seo404Log extends Model
{
    protected $table = 'seo_404_logs';

    public $timestamps = false;

    protected $fillable = [
        'path',
        'referrer',
        'user_agent',
        'ip',
        'hits',
        'first_seen_at',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'hits' => 'integer',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }
}
