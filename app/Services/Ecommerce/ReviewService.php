<?php

declare(strict_types=1);

namespace App\Services\Ecommerce;

use App\Facades\Hook;
use App\Models\Ecommerce\ProductReview;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    /**
     * Check if a user has purchased/claimed a product.
     */
    public function hasPurchased(User $user, Product $product): bool
    {
        // 1. Check orders table
        $hasOrder = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.user_id', $user->id)
            ->whereIn('orders.status', ['completed', 'processing'])
            ->where('order_items.product_id', $product->id)
            ->exists();

        if ($hasOrder) {
            return true;
        }

        // 2. Check user_subscriptions table if present
        if (\Illuminate\Support\Facades\Schema::hasTable('user_subscriptions')) {
            $hasSub = DB::table('user_subscriptions')
                ->where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->where('status', 'active')
                ->exists();
            if ($hasSub) {
                return true;
            }
        }

        // 3. Check external_claims table if present
        if (\Illuminate\Support\Facades\Schema::hasTable('external_claims')) {
            $itemIds = array_filter([
                data_get($product->settings, 'envato_item_id'),
                data_get($product->settings, 'codecanyon_item_id'),
                data_get($product->settings, 'themeforest_item_id'),
            ]);

            if (!empty($itemIds)) {
                $hasClaim = DB::table('external_claims')
                    ->where('user_id', $user->id)
                    ->whereIn('item_id', $itemIds)
                    ->exists();
                if ($hasClaim) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Submit a product review or comment
     *
     * SECURITY:
     * - Content sanitized (strip HTML)
     * - Only verified purchasers can provide a star rating (1-5 stars)
     * - Non-purchasers can only post comments (rating = null)
     * - Default status: pending (moderation queue)
     */
    public function submit(Product $product, User $user, array $data): ProductReview
    {
        // SECURITY: Sanitize content
        $title = strip_tags(trim($data['title'] ?? ''));
        $content = strip_tags(trim($data['content'] ?? ''));
        
        $verifiedPurchase = $this->hasPurchased($user, $product);

        // Only purchasers are allowed to give a Star Rating (1-5 stars)
        $rating = null;
        if ($verifiedPurchase && isset($data['rating']) && (int) $data['rating'] > 0) {
            $rating = max(1, min(5, (int) $data['rating']));
        }

        $review = ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'order_id' => $data['order_id'] ?? null,
            'rating' => $rating,
            'title' => $title ?: null,
            'content' => $content ?: null,
            'status' => 'pending',
            'verified_purchase' => $verifiedPurchase,
            'metadata' => $data['metadata'] ?? null,
        ]);

        Hook::doAction('review.submitted', $review);

        return $review;
    }

    /**
     * Approve a review and update product rating
     */
    public function approve(ProductReview $review): ProductReview
    {
        $review->update(['status' => 'approved']);

        $this->recalculateProductRating($review->product_id);

        Hook::doAction('review.approved', $review);

        return $review;
    }

    /**
     * Reject a review
     */
    public function reject(ProductReview $review, ?string $reason = null): ProductReview
    {
        $review->update([
            'status' => 'rejected',
            'metadata' => array_merge($review->metadata ?? [], [
                'rejection_reason' => $reason,
            ]),
        ]);

        Hook::doAction('review.rejected', $review);

        return $review;
    }

    /**
     * Recalculate denormalized avg_rating on product
     *
     * PERFORMANCE: Only aggregate ratings from approved reviews with rating > 0
     */
    public function recalculateProductRating(int $productId): void
    {
        $stats = ProductReview::where('product_id', $productId)
            ->where('status', 'approved')
            ->where('source', 'site')
            ->whereNotNull('rating')
            ->where('rating', '>', 0)
            ->selectRaw('COUNT(*) as count, COALESCE(AVG(rating), 0) as average')
            ->first();

        Product::where('id', $productId)->update([
            'avg_rating' => round((float) ($stats->average ?? 0), 2),
            'review_count' => (int) ($stats->count ?? 0),
        ]);
    }

    /**
     * Check if user can submit a review for a product
     */
    public function canSubmit(User $user, Product $product): bool
    {
        return (bool) Hook::applyFilters('review.can_submit', true, $user, $product);
    }
}
