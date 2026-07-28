<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Facades\Hook;
use App\Http\Resources\Api\V1\TagResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    /**
     * Display a listing of tags
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tag::query();

        // Locale filter
        if ($request->has('locale') && !empty($request->locale)) {
            $query->where('locale', $request->locale);
        }

        // Type filter
        if ($request->has('type')) {
            $query->where('type', $request->type);
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
            $response = $this->successResponse(TagResource::collection($tags->items()));
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

        return $this->successResponse(TagResource::collection($tags));
    }

    /**
     * Store a newly created tag
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Tag::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', 'in:post,product'],
            'locale' => ['nullable', 'string', 'max:10'],
            'translation_group_id' => ['nullable', 'string', 'max:36'],
        ]);

        $locale = $validated['locale'] ?? \Illuminate\Support\Facades\App::getLocale() ?: 'en';

        // Validate unique slug per type and locale
        $existing = Tag::where('type', $validated['type'])
            ->where('slug', $validated['slug'])
            ->where('locale', $locale)
            ->exists();

        if ($existing) {
            return $this->errorResponse('Slug already exists for this tag type and locale', 'VALIDATION_ERROR', [], 422);
        }

        $tag = Tag::create($validated);
        Hook::doAction('tag.saved', $tag, ['operation' => 'created']);

        return $this->successResponse(
            new TagResource($tag),
            'Tag created successfully',
            201
        );
    }

    /**
     * Display the specified tag
     */
    public function show(Tag $tag): JsonResponse
    {
        return $this->successResponse(new TagResource($tag));
    }

    /**
     * Update the specified tag
     */
    public function update(Request $request, Tag $tag): JsonResponse
    {
        $this->authorize('update', $tag);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['sometimes', 'required', 'string', 'in:post,product'],
            'locale' => ['nullable', 'string', 'max:10'],
            'translation_group_id' => ['nullable', 'string', 'max:36'],
        ]);

        // Validate unique slug per type and locale (excluding current)
        if (isset($validated['slug']) || isset($validated['type']) || isset($validated['locale'])) {
            $type = $validated['type'] ?? $tag->type;
            $slug = $validated['slug'] ?? $tag->slug;
            $locale = $validated['locale'] ?? $tag->locale ?? 'en';

            $existing = Tag::where('type', $type)
                ->where('slug', $slug)
                ->where('locale', $locale)
                ->where('id', '!=', $tag->id)
                ->exists();

            if ($existing) {
                return $this->errorResponse('Slug already exists for this tag type and locale', 'VALIDATION_ERROR', [], 422);
            }
        }

        $tag->update($validated);
        Hook::doAction('tag.saved', $tag, ['operation' => 'updated']);

        return $this->successResponse(
            new TagResource($tag),
            'Tag updated successfully'
        );
    }

    /**
     * Remove the specified tag
     */
    public function destroy(Tag $tag): JsonResponse
    {
        $this->authorize('delete', $tag);

        $tag->delete();
        Hook::doAction('tag.deleted', $tag, ['operation' => 'deleted']);

        return $this->successResponse(null, 'Tag deleted successfully', 204);
    }

    /**
     * Translate/Duplicate the specified tag to another locale
     */
    public function translate(Request $request, Tag $tag): JsonResponse
    {
        $this->authorize('update', $tag);
        $this->authorize('create', Tag::class);

        $validated = $request->validate([
            'locale' => ['required', 'string', 'max:10'],
        ]);

        $targetLocale = $validated['locale'];

        // Check if already exists
        $existing = Tag::where('translation_group_id', $tag->translation_group_id)
            ->where('locale', $targetLocale)
            ->first();

        if ($existing) {
            return $this->successResponse(new TagResource($existing), 'Translation already exists');
        }

        $newTag = $tag->replicate();
        $newTag->locale = $targetLocale;
        $newTag->translation_group_id = $tag->translation_group_id;
        $newTag->name = $tag->name . ' (' . strtoupper($targetLocale) . ')';
        $newTag->slug = $tag->slug;

        // Ensure slug is unique for this type and locale
        $baseSlug = $newTag->slug;
        $counter = 1;
        while (Tag::where('type', $newTag->type)->where('slug', $newTag->slug)->where('locale', $targetLocale)->exists()) {
            $newTag->slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $newTag->save();

        return $this->successResponse(new TagResource($newTag), 'Translation created successfully', 201);
    }
}
