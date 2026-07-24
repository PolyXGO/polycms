<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SearchSuggestionController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $limit = max(1, min((int) $request->query('limit', 6), 10));
        $scope = (string) $request->query('scope', 'posts');
        $locale = $this->resolveLocale($request);
        $safeQuery = str_replace(['%', '_'], ['\%', '\_'], $query);
        $like = "%{$safeQuery}%";

        $results = [];

        if (in_array($scope, ['all', 'posts', 'pages'], true)) {
            $types = $scope === 'pages' ? ['page'] : ['post'];
            if ($scope === 'all') {
                $types = ['post', 'page'];
            }

            $posts = Post::query()
                ->inLocale($locale)
                ->published()
                ->whereIn('type', $types)
                ->where(function ($builder) use ($like): void {
                    $builder->where('title', 'like', $like)
                        ->orWhere('excerpt', 'like', $like)
                        ->orWhere('content_html', 'like', $like);
                })
                ->orderByDesc('published_at')
                ->limit($limit)
                ->get(['id', 'title', 'slug', 'type', 'excerpt']);

            foreach ($posts as $post) {
                $results[] = [
                    'type' => $post->type === 'page' ? 'page' : 'post',
                    'title' => $post->title,
                    'subtitle' => $post->type === 'page' ? _l('Page') : _l('Post'),
                    'excerpt' => Str::limit(trim(strip_tags((string) $post->excerpt)), 90),
                    'url' => $this->absoluteUrl((string) $post->frontend_url),
                ];
            }
        }

        if (in_array($scope, ['all', 'products'], true) && count($results) < $limit) {
            $remaining = $limit - count($results);

            $products = Product::query()
                ->inLocale($locale)
                ->published()
                ->where(function ($builder) use ($like): void {
                    $builder->where('name', 'like', $like)
                        ->orWhere('sku', 'like', $like)
                        ->orWhere('short_description', 'like', $like)
                        ->orWhere('description_html', 'like', $like);
                })
                ->orderByDesc('published_at')
                ->limit($remaining)
                ->get(['id', 'name', 'slug', 'sku', 'short_description']);

            foreach ($products as $product) {
                $results[] = [
                    'type' => 'product',
                    'title' => $product->name,
                    'subtitle' => $product->sku ? _l('Product') . ' / SKU: ' . $product->sku : _l('Product'),
                    'excerpt' => Str::limit(trim(strip_tags((string) $product->short_description)), 90),
                    'url' => $this->absoluteUrl((string) $product->frontend_url),
                ];
            }
        }

        return response()->json([
            'results' => array_slice($results, 0, $limit),
        ]);
    }

    protected function absoluteUrl(string $url): string
    {
        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        return url(ltrim($url, '/'));
    }

    protected function resolveLocale(Request $request): string
    {
        $fallback = (string) (app()->getLocale() ?: config('app.locale', 'en'));
        $requested = strtolower(str_replace('_', '-', trim((string) $request->query('locale', ''))));
        $fallbackNormalized = strtolower(str_replace('_', '-', $fallback));

        $candidates = array_values(array_filter(array_unique([$requested, $fallbackNormalized])));

        if (!Schema::hasTable('languages')) {
            return $requested !== '' ? $requested : $fallback;
        }

        $activeLocales = Language::query()
            ->where('is_active', true)
            ->pluck('code')
            ->map(static fn ($code) => (string) $code)
            ->all();

        foreach ($candidates as $candidate) {
            foreach ($activeLocales as $activeLocale) {
                if (strtolower(str_replace('_', '-', $activeLocale)) === $candidate) {
                    return $activeLocale;
                }
            }
        }

        return $fallback;
    }
}
