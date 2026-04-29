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
        $reviews = ProductReview::where('product_id', $productId)
            ->approved()
            ->with('user:id,name,avatar')
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 10));

        // Stats
        $stats = ProductReview::where('product_id', $productId)
            ->approved()
            ->selectRaw('COUNT(*) as total, COALESCE(AVG(rating), 0) as average')
            ->first();

        return response()->json([
            'reviews' => $reviews,
            'stats' => [
                'total' => (int) $stats->total,
                'average' => round((float) $stats->average, 1),
            ],
        ]);
    }

    /**
     * Submit a new review (requires authentication)
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
            return response()->json(['message' => 'You have already reviewed this product'], 422);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:200',
            'content' => 'nullable|string|max:2000',
            'order_id' => 'nullable|integer|exists:orders,id',
        ]);

        $review = $this->reviewService->submit($product, $user, $validated);

        return response()->json([
            'message' => 'Review submitted for moderation',
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
