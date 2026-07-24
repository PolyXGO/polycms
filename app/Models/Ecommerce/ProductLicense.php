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
        return $this->subscription?->user();
    }

    public function product()
    {
        return $this->subscription?->product();
    }

    public function getUserAttribute()
    {
        return $this->subscription?->user;
    }

    public function getProductAttribute()
    {
        return $this->subscription?->product;
    }

    public function getOrderAttribute()
    {
        $sub = $this->subscription;
        if (!$sub) {
            return null;
        }

        $orderItem = OrderItem::where('product_id', $sub->product_id)
            ->where('service_id', $sub->service_id)
            ->whereHas('order', function ($query) use ($sub) {
                $query->where('user_id', $sub->user_id);
            })
            ->latest('id')
            ->first();

        return $orderItem?->order;
    }
}

