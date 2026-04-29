<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\ProductReview;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardStatsController extends Controller
{
    /**
     * Get e-commerce dashboard statistics.
     *
     * PERFORMANCE: Uses single aggregate queries, no N+1.
     * SECURITY: Requires authenticated admin.
     */
    public function index(Request $request): JsonResponse
    {
        $period = $request->get('period', '30'); // days
        $since = now()->subDays((int) $period);

        // Revenue & Order counts — single query
        $orderStats = Order::where('created_at', '>=', $since)
            ->selectRaw("
                COUNT(*) as total_orders,
                SUM(CASE WHEN status IN ('completed','processing') THEN total_amount ELSE 0 END) as total_revenue,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders
            ")
            ->first();

        // New customers in period
        $newCustomers = User::where('created_at', '>=', $since)->count();

        // Top Selling Products (by quantity) — single query, capped at 5
        $topProducts = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.created_at', '>=', $since)
            ->whereIn('orders.status', ['completed', 'processing'])
            ->select(
                'products.id',
                'products.name',
                'products.slug',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.total) as total_revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.slug')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        // Revenue trend — daily aggregation for chart
        $revenueTrend = Order::where('created_at', '>=', $since)
            ->whereIn('status', ['completed', 'processing'])
            ->selectRaw("DATE(created_at) as date, SUM(total_amount) as revenue, COUNT(*) as orders")
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Pending reviews count
        $pendingReviews = ProductReview::where('status', 'pending')->count();

        return response()->json([
            'revenue' => round((float) ($orderStats->total_revenue ?? 0), 2),
            'total_orders' => (int) ($orderStats->total_orders ?? 0),
            'pending_orders' => (int) ($orderStats->pending_orders ?? 0),
            'completed_orders' => (int) ($orderStats->completed_orders ?? 0),
            'new_customers' => $newCustomers,
            'pending_reviews' => $pendingReviews,
            'top_products' => $topProducts,
            'revenue_trend' => $revenueTrend,
            'period' => (int) $period,
        ]);
    }
}
