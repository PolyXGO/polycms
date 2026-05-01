<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderNote extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'user_id',
        'type',
        'content',
        'metadata',
        'is_customer_visible',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'is_customer_visible' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    // ─── Relationships ───────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // ─── Factory Methods ─────────────────────────────────────

    /**
     * Create a status change note
     */
    public static function statusChange(int $orderId, string $oldStatus, string $newStatus, ?int $userId = null): self
    {
        return static::create([
            'order_id' => $orderId,
            'user_id' => $userId,
            'type' => 'status_change',
            'content' => "Order status changed from \"{$oldStatus}\" to \"{$newStatus}\"",
            'metadata' => ['old_status' => $oldStatus, 'new_status' => $newStatus],
            'is_customer_visible' => true,
            'created_at' => now(),
        ]);
    }

    /**
     * Create a system note
     */
    public static function system(int $orderId, string $content, array $metadata = []): self
    {
        return static::create([
            'order_id' => $orderId,
            'user_id' => null,
            'type' => 'system',
            'content' => $content,
            'metadata' => $metadata ?: null,
            'is_customer_visible' => false,
            'created_at' => now(),
        ]);
    }

    /**
     * Create an admin note
     */
    public static function adminNote(int $orderId, string $content, int $userId, bool $customerVisible = false): self
    {
        return static::create([
            'order_id' => $orderId,
            'user_id' => $userId,
            'type' => 'note',
            'content' => $content,
            'is_customer_visible' => $customerVisible,
            'created_at' => now(),
        ]);
    }

    // ─── Scopes ──────────────────────────────────────────────

    /**
     * Only customer-visible notes
     */
    public function scopeCustomerVisible($query)
    {
        return $query->where('is_customer_visible', true);
    }
}
