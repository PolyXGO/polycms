<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;

class LicenseActivation extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'license_id', 'domain', 'hardware_id', 'ip_address', 'activation_token', 'activated_at'
    ];

    /**
     * Hide activation_token from default JSON serialization (admin listing, etc.)
     * to prevent accidental exposure. Only returned explicitly when needed.
     */
    protected $hidden = ['activation_token'];

    protected $casts = [
        'activated_at' => 'datetime',
    ];

    public function license()
    {
        return $this->belongsTo(ProductLicense::class);
    }
}
