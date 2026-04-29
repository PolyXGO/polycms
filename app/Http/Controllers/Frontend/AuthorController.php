<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Facades\Hook;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthorController extends Controller
{
    public function show(User $user, Request $request): View
    {
        $contentType = $request->get('type', 'post');
        $perPage = min((int) $request->get('per_page', 12), 50);
        $sortBy = $request->get('sort_by', $contentType === 'product' ? 'created_at' : 'published_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $data = [
            'author' => $user,
            'contentType' => $contentType,
        ];

        if ($contentType === 'product') {
            $query = Product::with(['categories', 'tags'])
                ->where('status', 'published')
                ->where('user_id', $user->id);

            $query->orderBy($sortBy, $sortOrder);
            $data['products'] = $query->paginate($perPage);
        } else {
            $query = Post::with(['categories', 'tags'])
                ->where('status', 'published')
                ->where('user_id', $user->id)
                ->where('type', $contentType === 'page' ? 'page' : 'post');

            $query->orderBy($sortBy, $sortOrder);
            $data['posts'] = $query->paginate($perPage);
        }

        $data = Hook::applyFilters('theme.view.data', $data, 'authors.show');

        return view('authors.show', $data);
    }
}

