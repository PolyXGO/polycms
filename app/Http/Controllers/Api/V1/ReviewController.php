<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\ProductReview;
use App\Models\Product;
use App\Services\Ecommerce\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        protected ReviewService $reviewService
    ) {}

    /**
     * List approved reviews for a product
     */
    public function index(Request $request, int $productId): JsonResponse
    {
        // Paginate local reviews only
        $reviews = ProductReview::where('product_id', $productId)
            ->approved()
            ->with('user:id,name,avatar')
            ->orderByDesc('created_at')
            ->paginate((int) $request->get('per_page', 10));

        // Compute combined stats (local + external/synced)
        $localStats = ProductReview::where('product_id', $productId)
            ->approved()
            ->whereNotNull('rating')
            ->where('rating', '>', 0)
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(rating), 0) as rating_sum')
            ->first();

        $localCount = (int) ($localStats->total ?? 0);
        $localSum = (float) ($localStats->rating_sum ?? 0.0);

        // Fetch product to get external stats
        $product = \App\Models\Product::find($productId);
        $externalCount = 0;
        $externalAvg = 0.0;
        if ($product) {
            $externalCount = (int) data_get($product->settings, 'external_rating_count', 0);
            $externalAvg = (float) data_get($product->settings, 'external_rating', 0.0);
        }

        $totalReviews = $localCount + $externalCount;
        $ratingSum = $localSum + ($externalAvg * $externalCount);
        $averageRating = $totalReviews > 0 ? ($ratingSum / $totalReviews) : 0.0;

        return response()->json([
            'reviews' => $reviews,
            'stats' => [
                'total' => $totalReviews,
                'average' => round($averageRating, 1),
            ],
        ]);
    }

    /**
     * Submit a new review or comment (requires authentication)
     */
    public function store(Request $request, int $productId): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Authentication required'], 401);
        }

        $product = Product::findOrFail($productId);

        // Check if user can submit
        if (!$this->reviewService->canSubmit($user, $product)) {
            return response()->json(['message' => 'You cannot submit feedback for this product'], 422);
        }

        $validated = $request->validate([
            'rating' => 'nullable|integer|min:1|max:5',
            'title' => 'nullable|string|max:200',
            'content' => 'required|string|max:2000',
            'order_id' => 'nullable|integer|exists:orders,id',
        ]);

        $review = $this->reviewService->submit($product, $user, $validated);

        return response()->json([
            'message' => 'Feedback submitted for moderation',
            'review' => $review,
        ], 201);
    }

    /**
     * Admin: List all reviews (with moderation status filter)
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $query = ProductReview::with(['product:id,name,slug', 'user:id,name,email']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $reviews = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($reviews);
    }

    /**
     * Admin: Approve a review
     */
    public function approve(Request $request, int $reviewId): JsonResponse
    {
        $review = ProductReview::findOrFail($reviewId);
        $this->reviewService->approve($review);

        return response()->json(['message' => 'Review approved', 'review' => $review->fresh()]);
    }

    /**
     * Admin: Reject a review
     */
    public function reject(Request $request, int $reviewId): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $review = ProductReview::findOrFail($reviewId);
        $this->reviewService->reject($review, $validated['reason'] ?? null);

        return response()->json(['message' => 'Review rejected', 'review' => $review->fresh()]);
    }
}
