<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Facades\Hook;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBrand;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function show(string $slug, Request $request): View
    {
        $brand = ProductBrand::where('slug', $slug)->firstOrFail();

        $query = Product::with(['categories', 'tags', 'brands'])
            ->where('status', 'published')
            ->whereHas('brands', function ($q) use ($brand) {
                $q->where('product_brands.id', $brand->id);
            });

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = min((int) $request->get('per_page', 12), 50);
        $products = $query->paginate($perPage);

        $data = [
            'brand' => $brand,
            'products' => $products,
            'contentType' => 'product',
        ];

        $data = Hook::applyFilters('theme.view.data', $data, 'brands.show');

        return view('brands.show', $data);
    }
}
