<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the homepage
     */
    public function index(Request $request): View
    {
        // Get recent posts
        $posts = Post::with(['user', 'categories'])
            ->where('status', 'published')
            ->where('type', 'post')
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        // Get featured products
        $products = Product::with(['categories'])
            ->where('status', 'published')
            ->where('featured', true)
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        // Prepare data for view
        $data = [
            'posts' => $posts,
            'products' => $products,
        ];

        // Apply theme filter
        $data = \App\Facades\Hook::applyFilters('theme.view.data', $data, 'home');

        // Try to render theme view first, fallback to default
        if (view()->exists('home')) {
            return view('home', $data);
        }

        // Fallback: render posts index
        return view('posts.index', $data);
    }
}