<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreLayoutAssetRequest;
use App\Http\Requests\Api\V1\UpdateLayoutAssetRequest;
use App\Http\Resources\Api\V1\LayoutAssetCollection;
use App\Http\Resources\Api\V1\LayoutAssetResource;
use App\Models\LayoutAsset;
use App\Services\LayoutAssetManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class LayoutAssetController extends Controller
{
    public function __construct(
        protected LayoutAssetManager $layoutAssetManager
    ) {}

    public function index(Request $request): LayoutAssetCollection|JsonResponse
    {
        if ($request->user()->cannot('viewAny', LayoutAsset::class)) {
            return $this->forbiddenResponse();
        }

        $this->layoutAssetManager->ensureStorageReady();

        $request->merge($this->normalizeFilterScalars($request, [
            'kind',
            'search',
            'category',
            'source',
            'applies_to',
            'sort_by',
            'sort_order',
            'per_page',
            'page',
        ]));

        $validated = $request->validate([
            'kind' => ['required', Rule::in(['part', 'template'])],
            'search' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:120'],
            'source' => ['nullable', Rule::in(['all', 'system', 'custom'])],
            'applies_to' => ['nullable', Rule::in(['page', 'post', 'news'])],
            'sort_by' => ['nullable', Rule::in(['name', 'updated_at', 'created_at'])],
            'sort_order' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if (!$this->hasLayoutAssetsTable()) {
            return new LayoutAssetCollection(
                new LengthAwarePaginator(
                    collect(),
                    0,
                    $validated['per_page'] ?? 18,
                    max((int) $request->integer('page', 1), 1)
                )
            );
        }

        $query = LayoutAsset::query()
            ->with('user:id,name')
            ->where('kind', $validated['kind']);

        if ($this->hasAssignedPostsRelation()) {
            $query->withCount('assignedPosts');
        }

        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($validated['category'])) {
            $query->where('category', $validated['category']);
        }

        if (($validated['source'] ?? 'all') === 'system') {
            $query->where('is_system', true);
        } elseif (($validated['source'] ?? 'all') === 'custom') {
            $query->where('is_system', false);
        }

        if (!empty($validated['applies_to']) && $validated['kind'] === 'template') {
            $query->whereJsonContains('applies_to', $validated['applies_to']);
        }

        $aggregationQuery = LayoutAsset::query()->where('kind', $validated['kind']);
        
        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $aggregationQuery->where(function ($builder) use ($search): void {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (($validated['source'] ?? 'all') === 'system') {
            $aggregationQuery->where('is_system', true);
        } elseif (($validated['source'] ?? 'all') === 'custom') {
            $aggregationQuery->where('is_system', false);
        }

        if (!empty($validated['applies_to']) && $validated['kind'] === 'template') {
            $aggregationQuery->whereJsonContains('applies_to', $validated['applies_to']);
        }

        $aggregations = $aggregationQuery
            ->select('category', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        $query->orderBy($validated['sort_by'] ?? 'updated_at', $validated['sort_order'] ?? 'desc');

        return (new LayoutAssetCollection($query->paginate($validated['per_page'] ?? 18)))
            ->additional([
                'aggregations' => [
                    'categories' => $aggregations,
                ],
            ]);
    }

    public function store(StoreLayoutAssetRequest $request): JsonResponse
    {
        $this->layoutAssetManager->ensureStorageReady();

        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['slug'] = $data['slug'] ?? $this->layoutAssetManager->generateUniqueSlug($data['name']);
        $data['is_system'] = false;
        $data['source_type'] = 'custom';
        $data['source_name'] = $request->user()->name;
        $data['content_html'] = $this->layoutAssetManager->renderContent($data['content_raw'] ?? null);

        $asset = LayoutAsset::create($data)->load('user:id,name');

        return $this->successResponse(
            new LayoutAssetResource($asset),
            ucfirst($asset->kind) . ' created successfully',
            201
        );
    }

    public function show(LayoutAsset $layoutAsset): JsonResponse
    {
        if (request()->user()->cannot('view', $layoutAsset)) {
            return $this->forbiddenResponse();
        }

        $layoutAsset->load('user:id,name');

        if ($this->hasAssignedPostsRelation()) {
            $layoutAsset->loadCount('assignedPosts');
        }

        return $this->successResponse(
            new LayoutAssetResource($layoutAsset)
        );
    }

    public function update(UpdateLayoutAssetRequest $request, LayoutAsset $layoutAsset): JsonResponse
    {
        if ($layoutAsset->is_system) {
            return $this->forbiddenResponse('System assets are read-only. Duplicate it to customize.');
        }

        $data = $request->validated();
        if (array_key_exists('name', $data) && !array_key_exists('slug', $data)) {
            $data['slug'] = $this->layoutAssetManager->generateUniqueSlug($data['name'], (int) $layoutAsset->id);
        }

        if (array_key_exists('content_raw', $data)) {
            $data['content_html'] = $this->layoutAssetManager->renderContent($data['content_raw'] ?? null);
        }

        // If preview_image is explicitly set to null, delete the physical thumbnail to force regeneration
        if (array_key_exists('preview_image', $data) && $data['preview_image'] === null) {
            if ($layoutAsset->preview_image) {
                $oldPath = preg_replace('#^.*/storage/#', '', $layoutAsset->preview_image);
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                }
            }
        }

        $layoutAsset->update($data);

        $layoutAsset = $layoutAsset->fresh()->load('user:id,name');
        if ($this->hasAssignedPostsRelation()) {
            $layoutAsset->loadCount('assignedPosts');
        }

        return $this->successResponse(
            new LayoutAssetResource($layoutAsset),
            ucfirst($layoutAsset->kind) . ' updated successfully'
        );
    }

    public function uploadThumbnail(\Illuminate\Http\Request $request, LayoutAsset $layoutAsset): JsonResponse
    {
        $request->validate([
            'preview_image_base64' => 'required|string',
        ]);

        $base64 = $request->input('preview_image_base64');
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
            $base64 = substr($base64, strpos($base64, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, gif

            if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                return $this->errorResponse('Invalid image type', 400);
            }

            $base64 = str_replace(' ', '+', $base64);
            $image = base64_decode($base64);

            if ($image === false) {
                return $this->errorResponse('base64_decode failed', 400);
            }
        } else {
            return $this->errorResponse('Did not match data URI with image data', 400);
        }

        if ($layoutAsset->preview_image) {
            $oldPath = preg_replace('#^.*/storage/#', '', $layoutAsset->preview_image);
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
            }
        }

        $fileName = $layoutAsset->id . '-' . time() . '.' . $type;
        $path = 'layout-assets/thumbnails/' . $fileName;

        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $image);

        $url = '/storage/' . $path;
        $layoutAsset->update(['preview_image' => $url]);

        return $this->successResponse(
            new LayoutAssetResource($layoutAsset->fresh()),
            'Thumbnail uploaded successfully'
        );
    }

    public function destroy(LayoutAsset $layoutAsset): JsonResponse
    {
        if (request()->user()->cannot('delete', $layoutAsset)) {
            return $this->forbiddenResponse();
        }

        if ($layoutAsset->preview_image) {
            $oldPath = preg_replace('#^.*/storage/#', '', $layoutAsset->preview_image);
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
            }
        }

        $layoutAsset->delete();

        return $this->successResponse(null, ucfirst($layoutAsset->kind) . ' deleted successfully', 204);
    }

    public function duplicate(Request $request, LayoutAsset $layoutAsset): JsonResponse
    {
        if ($request->user()->cannot('create', LayoutAsset::class)) {
            return $this->forbiddenResponse();
        }

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $duplicate = $this->layoutAssetManager
            ->duplicate($layoutAsset, $request->user(), $validated['name'] ?? null)
            ->load('user:id,name');

        return $this->successResponse(
            new LayoutAssetResource($duplicate),
            ucfirst($layoutAsset->kind) . ' duplicated successfully',
            201
        );
    }

    protected function hasLayoutAssetsTable(): bool
    {
        return Schema::hasTable('layout_assets');
    }

    protected function hasAssignedPostsRelation(): bool
    {
        return $this->hasLayoutAssetsTable()
            && Schema::hasTable('posts')
            && Schema::hasColumn('posts', 'layout_template_id');
    }

    /**
     * Some admin views can accidentally resend scalar filters as repeated query params.
     * Collapse them back to the first scalar value before validation.
     *
     * @param  array<int, string>  $keys
     * @return array<string, mixed>
     */
    protected function normalizeFilterScalars(Request $request, array $keys): array
    {
        $normalized = [];

        foreach ($keys as $key) {
            $value = $request->input($key);
            if (!is_array($value)) {
                continue;
            }

            $normalized[$key] = $value[0] ?? null;
        }

        return $normalized;
    }
}
