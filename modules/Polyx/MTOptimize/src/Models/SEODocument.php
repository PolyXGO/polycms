<?php

declare(strict_types=1);

namespace Modules\Polyx\MTOptimize\Models;

use Illuminate\Database\Eloquent\Model;

class SEODocument extends Model
{
    protected $table = 'seo_documents';

    protected $fillable = [
        'site_id',
        'locale',
        'object_type',
        'object_id',
        'route_fingerprint',
        'payload_json',
        'checksum',
        'resolved_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'payload_json' => 'array',
            'resolved_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}
