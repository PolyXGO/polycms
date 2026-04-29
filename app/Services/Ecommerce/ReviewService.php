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
     * Submit a product review
     *
     * SECURITY:
     * - One review per user per product (unique constraint)
     * - Content sanitized (strip HTML)
     * - Auto-detect verified purchase
     * - Default status: pending (moderation queue)
     */
    public function submit(Product $product, User $user, array $data): ProductReview
    {
        // SECURITY: Sanitize content
        $title = strip_tags(trim($data['title'] ?? ''));
        $content = strip_tags(trim($data['content'] ?? ''));
        $rating = max(1, min(5, (int) ($data['rating'] ?? 5)));

        // Check if user has a completed order with this product
        $verifiedPurchase = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.user_id', $user->id)
            ->where('orders.status', 'completed')
            ->where('order_items.product_id', $product->id)
            ->exists();

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
     * PERFORMANCE: Single aggregate query instead of loading all reviews
     */
    public function recalculateProductRating(int $productId): void
    {
        $stats = ProductReview::where('product_id', $productId)
            ->where('status', 'approved')
            ->selectRaw('COUNT(*) as count, COALESCE(AVG(rating), 0) as average')
            ->first();

        Product::where('id', $productId)->update([
            'avg_rating' => round((float) $stats->average, 2),
            'review_count' => (int) $stats->count,
        ]);
    }

    /**
     * Check if user can submit a review for a product
     */
    public function canSubmit(User $user, Product $product): bool
    {
        // Already reviewed?
        $exists = ProductReview::where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($exists) {
            return false;
        }

        return (bool) Hook::applyFilters('review.can_submit', true, $user, $product);
    }
}
