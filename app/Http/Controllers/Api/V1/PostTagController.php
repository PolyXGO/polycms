<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\AuthorizesPermissions;
use App\Http\Resources\Api\V1\PostTagResource;
use App\Models\PostTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostTagController extends Controller
{
    use AuthorizesPermissions;

    /**
     * Display a listing of post tags
     */
    public function index(Request $request): JsonResponse
    {
        $query = PostTag::query();

        // Locale filter
        if ($request->has('locale') && !empty($request->locale)) {
            $query->where('locale', $request->locale);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort
        $query->orderBy('name');

        // Paginate if needed
        if ($request->has('per_page')) {
            $perPage = min($request->get('per_page', 15), 100);
            $tags = $query->paginate($perPage);
            $response = $this->successResponse(PostTagResource::collection($tags->items()));
            $response->getData()->meta = [
                'pagination' => [
                    'total' => $tags->total(),
                    'per_page' => $tags->perPage(),
                    'current_page' => $tags->currentPage(),
                    'last_page' => $tags->lastPage(),
                ],
            ];
            return $response;
        }

        $tags = $query->get();

        return $this->successResponse(PostTagResource::collection($tags));
    }

    /**
     * Store a newly created post tag
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorizePermission($request, 'create tag');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'locale' => ['nullable', 'string', 'max:10'],
            'translation_group_id' => ['nullable', 'string', 'max:36'],
        ]);

        $locale = $validated['locale'] ?? \Illuminate\Support\Facades\App::getLocale() ?: 'en';
        $slug = $validated['slug'] ?? \Illuminate\Support\Str::slug($validated['name']);

        // Validate unique slug per locale
        $existing = PostTag::where('slug', $slug)
            ->where('locale', $locale)
            ->exists();

        if ($existing) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'slug' => ['The slug has already been taken.'],
            ]);
        }

        $validated['slug'] = $slug;
        $tag = PostTag::create($validated);

        return $this->successResponse(
            new PostTagResource($tag),
            'Post tag created successfully',
            201
        );
    }

    /**
     * Display the specified post tag
     */
    public function show(PostTag $postTag): JsonResponse
    {
        return $this->successResponse(new PostTagResource($postTag));
    }

    /**
     * Update the specified post tag
     */
    public function update(Request $request, PostTag $postTag): JsonResponse
    {
        $this->authorizePermission($request, 'update tag');

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'locale' => ['nullable', 'string', 'max:10'],
            'translation_group_id' => ['nullable', 'string', 'max:36'],
        ]);

        if (isset($validated['slug']) || isset($validated['locale'])) {
            $slug = $validated['slug'] ?? $postTag->slug;
            $locale = $validated['locale'] ?? $postTag->locale ?? 'en';

            $existing = PostTag::where('slug', $slug)
                ->where('locale', $locale)
                ->where('id', '!=', $postTag->id)
                ->exists();

            if ($existing) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'slug' => ['The slug has already been taken.'],
                ]);
            }
        }

        $postTag->update($validated);

        return $this->successResponse(
            new PostTagResource($postTag),
            'Post tag updated successfully'
        );
    }

    /**
     * Remove the specified post tag
     */
    public function destroy(Request $request, PostTag $postTag): JsonResponse
    {
        $this->authorizePermission($request, 'delete tag');

        $postTag->delete();

        return $this->successResponse(null, 'Post tag deleted successfully', 204);
    }

    /**
     * Translate/Duplicate the specified post tag to another locale
     */
    public function translate(Request $request, PostTag $postTag): JsonResponse
    {
        $this->authorizePermission($request, ['update tag', 'create tag'], true);

        $validated = $request->validate([
            'locale' => ['required', 'string', 'max:10'],
        ]);

        $targetLocale = $validated['locale'];

        // Check if already exists
        $existing = PostTag::where('translation_group_id', $postTag->translation_group_id)
            ->where('locale', $targetLocale)
            ->first();

        if ($existing) {
            return $this->successResponse(new PostTagResource($existing), 'Translation already exists');
        }

        $newTag = $postTag->replicate();
        $newTag->locale = $targetLocale;
        $newTag->translation_group_id = $postTag->translation_group_id;
        $newTag->name = $postTag->name . ' (' . strtoupper($targetLocale) . ')';
        $newTag->slug = $postTag->slug;

        // Ensure slug is unique for this locale
        $baseSlug = $newTag->slug;
        $counter = 1;
        while (PostTag::where('slug', $newTag->slug)->where('locale', $targetLocale)->exists()) {
            $newTag->slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $newTag->save();

        return $this->successResponse(new PostTagResource($newTag), 'Translation created successfully', 201);
    }
}
