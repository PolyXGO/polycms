<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Display a static page
     */
    public function show(string $slug): View
    {
        $page = Post::with(['user'])
            ->where('slug', $slug)
            ->where('type', 'page')
            ->where('status', 'published')
            ->firstOrFail();

        $data = [
            'page' => $page,
        ];

        // Apply theme filter
        $data = \App\Facades\Hook::applyFilters('theme.view.data', $data, 'pages.show');

        return view('pages.show', $data);
    }
}