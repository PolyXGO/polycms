<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CorePreset extends Model
{
    use HasFactory;

    protected $table = 'polycms_presets';

    protected $fillable = [
        'uuid',
        'type',
        'category_id',
        'name',
        'description',
        'payload',
        'is_global',
        'is_system',
    ];

    protected $casts = [
        'payload' => 'array',
        'is_global' => 'boolean',
        'is_system' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(CorePresetCategory::class, 'category_id');
    }
}
