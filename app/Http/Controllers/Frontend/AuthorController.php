<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Facades\Hook;
use App\Http\Controllers\Frontend\FrontendController;
use App\Models\Post;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthorController extends FrontendController
{
    public function show(User $user, Request $request): View
    {
        $contentType = $request->get('type', 'post');
        $perPage = min((int) $request->get('per_page', 12), 50);
        $sortBy = $request->get('sort_by', $contentType === 'product' ? 'created_at' : 'published_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $isAdmin = $this->isAdmin($request);

        $data = [
            'author' => $user,
            'contentType' => $contentType,
        ];

        if ($contentType === 'product') {
            $query = Product::with(['categories', 'tags'])
                ->where('user_id', $user->id);

            if (!$isAdmin) {
                $query->where('status', 'published');
            }

            $query->orderBy($sortBy, $sortOrder);
            $data['products'] = $query->paginate($perPage)->withQueryString();
        } else {
            $query = Post::with(['categories', 'tags'])
                ->where('user_id', $user->id)
                ->where('type', $contentType === 'page' ? 'page' : 'post');

            if (!$isAdmin) {
                $query->where('status', 'published');
            }

            $query->orderBy($sortBy, $sortOrder);
            $data['posts'] = $query->paginate($perPage)->withQueryString();
        }

        $data = Hook::applyFilters('theme.view.data', $data, 'authors.show');

        return view('authors.show', $data);
    }
}

