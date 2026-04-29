<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;

class ProductLicense extends Model
{
    protected $fillable = [
        'subscription_id', 'license_key',
        'max_activations', 'activation_count', 'status'
    ];

    public function subscription()
    {
        return $this->belongsTo(UserSubscription::class);
    }

    public function activations()
    {
        return $this->hasMany(LicenseActivation::class, 'license_id');
    }

    public function user()
    {
        return $this->subscription->user();
    }

    public function product()
    {
        return $this->subscription->product();
    }
}
