<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\Order;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GlobalSearchController extends Controller
{
    /**
     * Global admin search across multiple entity types.
     *
     * SECURITY: Requires authenticated admin session.
     * PERFORMANCE: Uses LIKE with limit caps per type (max 5 each).
     *              Short-circuits on empty query.
     */
    public function search(Request $request): JsonResponse
    {
        $query = trim($request->get('q', ''));

        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        // SECURITY: Sanitize for LIKE injection
        $safeQuery = str_replace(['%', '_'], ['\%', '\_'], $query);
        $likePattern = "%{$safeQuery}%";

        $results = [];

        // Products
        $products = Product::where('name', 'LIKE', $likePattern)
            ->orWhere('sku', 'LIKE', $likePattern)
            ->select('id', 'name', 'slug', 'sku', 'price')
            ->limit(5)
            ->get()
            ->map(fn($p) => [
                'type' => 'product',
                'id' => $p->id,
                'title' => $p->name,
                'subtitle' => $p->sku ? "SKU: {$p->sku}" : null,
                'url' => "/admin/products/{$p->id}/edit",
            ]);
        $results = array_merge($results, $products->toArray());

        // Posts
        $posts = Post::where('title', 'LIKE', $likePattern)
            ->select('id', 'title', 'slug', 'type', 'status')
            ->limit(5)
            ->get()
            ->map(fn($p) => [
                'type' => $p->type === 'page' ? 'page' : 'post',
                'id' => $p->id,
                'title' => $p->title,
                'subtitle' => ucfirst($p->status),
                'url' => $p->type === 'page'
                    ? "/admin/pages/{$p->id}/edit"
                    : "/admin/posts/{$p->id}/edit",
            ]);
        $results = array_merge($results, $posts->toArray());

        // Orders
        $orders = Order::where('code', 'LIKE', $likePattern)
            ->orWhere('guest_email', 'LIKE', $likePattern)
            ->select('id', 'code', 'status', 'total_amount', 'currency')
            ->limit(5)
            ->get()
            ->map(fn($o) => [
                'type' => 'order',
                'id' => $o->id,
                'title' => "#{$o->code}",
                'subtitle' => ucfirst($o->status) . " — {$o->total_amount} {$o->currency}",
                'url' => "/admin/orders/{$o->id}",
            ]);
        $results = array_merge($results, $orders->toArray());

        // Users
        $users = \App\Models\User::where('name', 'LIKE', $likePattern)
            ->orWhere('email', 'LIKE', $likePattern)
            ->select('id', 'name', 'email')
            ->limit(5)
            ->get()
            ->map(fn($u) => [
                'type' => 'user',
                'id' => $u->id,
                'title' => $u->name,
                'subtitle' => $u->email,
                'url' => "/admin/users/{$u->id}/edit",
            ]);
        $results = array_merge($results, $users->toArray());

        return response()->json(['results' => $results]);
    }
}
