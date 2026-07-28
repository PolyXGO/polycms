<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;

class LicenseActivation extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'license_id', 'domain', 'hardware_id', 'ip_address', 'activated_at'
    ];

    protected $casts = [
        'activated_at' => 'datetime',
    ];

    public function license()
    {
        return $this->belongsTo(ProductLicense::class);
    }
}
