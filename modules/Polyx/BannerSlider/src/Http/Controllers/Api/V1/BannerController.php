<?php

declare(strict_types=1);

namespace Modules\Polyx\BannerSlider\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Polyx\BannerSlider\Http\Requests\StoreBannerRequest;
use Modules\Polyx\BannerSlider\Http\Requests\UpdateBannerRequest;
use Modules\Polyx\BannerSlider\Http\Resources\BannerResource;
use Modules\Polyx\BannerSlider\Models\BannerSlider;

class BannerController extends Controller
{
    /**
     * Display a listing of banners
     */
    public function index(Request $request): JsonResponse
    {
        $query = BannerSlider::with('image');

        // Active filter
        if ($request->has('active')) {
            $query->where('active', filter_var($request->active, FILTER_VALIDATE_BOOLEAN));
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort by order (default)
        $sortBy = $request->get('sort_by', 'order');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = min($request->get('per_page', 15), 100);
        $banners = $query->paginate($perPage);

        return response()->json([
            'data' => BannerResource::collection($banners->items()),
            'meta' => [
                'current_page' => $banners->currentPage(),
                'last_page' => $banners->lastPage(),
                'per_page' => $banners->perPage(),
                'total' => $banners->total(),
                'from' => $banners->firstItem(),
                'to' => $banners->lastItem(),
            ],
        ]);
    }

    /**
     * Store a newly created banner
     */
    public function store(StoreBannerRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Set order automatically based on created_at timestamp if not provided
        if (!isset($data['order'])) {
            $data['order'] = now()->timestamp;
        }

        $banner = BannerSlider::create($data);

        $banner->load('image');

        return $this->successResponse(
            new BannerResource($banner),
            'Banner created successfully',
            201
        );
    }

    /**
     * Display the specified banner
     */
    public function show(BannerSlider $banner): JsonResponse
    {
        $banner->load('image');

        return $this->successResponse(new BannerResource($banner));
    }

    /**
     * Update the specified banner
     */
    public function update(UpdateBannerRequest $request, BannerSlider $banner): JsonResponse
    {
        $banner->update($request->validated());
        $banner->load('image');

        return $this->successResponse(
            new BannerResource($banner),
            'Banner updated successfully'
        );
    }

    /**
     * Remove the specified banner
     */
    public function destroy(BannerSlider $banner): JsonResponse
    {
        $banner->delete();

        return $this->successResponse(null, 'Banner deleted successfully', 204);
    }

    /**
     * Reorder banners
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'banners' => 'required|array',
            'banners.*.id' => 'required|exists:banner_sliders,id',
            'banners.*.order' => 'required|integer',
        ]);

        foreach ($request->banners as $item) {
            BannerSlider::where('id', $item['id'])
                ->update(['order' => $item['order']]);
        }

        return $this->successResponse(null, 'Banners reordered successfully');
    }

    /**
     * Toggle banner active status
     */
    public function toggleActive(BannerSlider $banner): JsonResponse
    {
        $banner->active = !$banner->active;
        $banner->save();
        $banner->load('image');

        return $this->successResponse(
            new BannerResource($banner),
            $banner->active ? 'Banner activated successfully' : 'Banner deactivated successfully'
        );
    }

    /**
     * Duplicate a banner
     */
    public function duplicate(BannerSlider $banner): JsonResponse
    {
        // Get all attributes except id and timestamps
        $attributes = $banner->getAttributes();
        unset($attributes['id'], $attributes['created_at'], $attributes['updated_at'], $attributes['deleted_at']);

        // Add "Copy" to title if it exists
        if (isset($attributes['title']) && !empty($attributes['title'])) {
            $attributes['title'] = $attributes['title'] . ' (Copy)';
        }

        // Set order to current timestamp to place it at the end
        $attributes['order'] = now()->timestamp;

        // Set active to false by default for duplicated banner
        $attributes['active'] = false;

        // Create the duplicate
        $duplicate = BannerSlider::create($attributes);
        $duplicate->load('image');

        return $this->successResponse(
            new BannerResource($duplicate),
            'Banner duplicated successfully',
            201
        );
    }
}
