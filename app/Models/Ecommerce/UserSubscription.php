<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Product;

class UserSubscription extends Model
{
    protected $fillable = [
        'user_id', 'service_id', 'product_id',
        'starts_at', 'expires_at', 'status',
        'is_auto_renew', 'gateway_profile_id', 'renewed_from_subscription_id'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_auto_renew' => 'boolean',
    ];

    protected $appends = ['paid_price'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(ProductService::class);
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getOrderItemAttribute()
    {
        $subId = (int) $this->id;

        $matchedItem = OrderItem::whereHas('order', function ($query) {
                $query->where('user_id', $this->user_id);
            })
            ->where(function ($query) {
                $query->where('service_id', $this->service_id)
                      ->orWhere('product_id', $this->product_id);
            })
            ->get()
            ->first(function ($item) use ($subId) {
                $meta = $item->metadata ?? [];
                if (isset($meta['subscription_id']) && (int)$meta['subscription_id'] === $subId) {
                    return true;
                }
                if (isset($meta['subscription_ids']) && is_array($meta['subscription_ids']) && in_array($subId, array_map('intval', $meta['subscription_ids']), true)) {
                    return true;
                }
                return false;
            });

        if ($matchedItem) {
            return $matchedItem;
        }

        return OrderItem::where('product_id', $this->product_id)
            ->whereHas('order', function ($query) {
                $query->where('user_id', $this->user_id);
            })
            ->oldest('id')
            ->first();
    }

    public function getPaidPriceAttribute()
    {
        $item = $this->order_item;
        if ($item !== null && $item->total !== null) {
            return (float) $item->total;
        }
        $order = $this->order;
        if ($order !== null && $order->total_amount !== null) {
            return (float) $order->total_amount;
        }
        return (float) ($this->service?->price ?? $this->product?->price ?? 0);
    }

    public function getOrderAttribute()
    {
        return $this->order_item?->order;
    }
}

