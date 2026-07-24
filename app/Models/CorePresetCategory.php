<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorePresetCategory extends Model
{
    use HasFactory;

    protected $table = 'polycms_preset_categories';

    protected $fillable = [
        'parent_id',
        'name',
        'description',
        'type',
    ];

    public function parent()
    {
        return $this->belongsTo(CorePresetCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(CorePresetCategory::class, 'parent_id');
    }

    public function presets()
    {
        return $this->hasMany(CorePreset::class, 'category_id');
    }
}
