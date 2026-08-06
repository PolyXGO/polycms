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

    public function getOrderItemAttribute()
    {
        $sub = $this->subscription;
        if (!$sub) {
            return null;
        }

        $subId = (int) $sub->id;
        $licId = (int) $this->id;

        $orderItem = OrderItem::whereHas('order', function ($query) use ($sub) {
                $query->where('user_id', $sub->user_id);
            })
            ->where(function ($query) use ($sub) {
                $query->where('service_id', $sub->service_id)
                      ->orWhere('product_id', $sub->product_id);
            })
            ->get()
            ->first(function ($item) use ($subId, $licId) {
                $meta = $item->metadata ?? [];
                if (isset($meta['licenses']) && is_array($meta['licenses'])) {
                    foreach ($meta['licenses'] as $lic) {
                        if (isset($lic['id']) && (int)$lic['id'] === $licId) {
                            return true;
                        }
                    }
                }
                if (isset($meta['license_id']) && (int)$meta['license_id'] === $licId) {
                    return true;
                }
                if (isset($meta['subscription_id']) && (int)$meta['subscription_id'] === $subId) {
                    return true;
                }
                if (isset($meta['subscription_ids']) && is_array($meta['subscription_ids']) && in_array($subId, array_map('intval', $meta['subscription_ids']), true)) {
                    return true;
                }
                return false;
            });

        return $orderItem ?? $sub->order_item;
    }

    public function getOrderAttribute()
    {
        return $this->order_item?->order ?? $this->subscription?->order;
    }
}

