<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;

    protected $casts = [
        'is_system' => 'boolean',
        'metadata' => 'array',
    ];

    protected $fillable = [
        'name',
        'guard_name',
        'is_system',
        'module_owner',
        'metadata',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.model_morph_key'),
            'role_id'
        );
    }
}

