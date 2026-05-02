<?php

declare(strict_types=1);

namespace Modules\Polyx\MTOptimize\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\Polyx\MTOptimize\Models\Seo404Log;
use Modules\Polyx\MTOptimize\Models\SeoRedirect;

class SeoToolsController extends Controller
{
    // ─── Redirects CRUD ───────────────────────────────────────────

    public function redirectIndex(Request $request): JsonResponse
    {
        $query = SeoRedirect::query()->orderByDesc('updated_at');

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->where('from_path', 'like', $search)
                  ->orWhere('to_url', 'like', $search)
                  ->orWhere('note', 'like', $search);
            });
        }

        return response()->json([
            'data' => $query->paginate($request->integer('per_page', 25)),
        ]);
    }

    public function redirectStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_path' => 'required|string|max:2048',
            'to_url' => 'required|string|max:2048',
            'type' => 'required|integer|in:301,302',
            'is_active' => 'boolean',
            'note' => 'nullable|string|max:500',
        ]);

        $validated['from_path'] = '/' . ltrim($validated['from_path'], '/');

        $redirect = SeoRedirect::create($validated);
        $this->clearRedirectCache($validated['from_path']);

        return response()->json(['data' => $redirect, 'message' => 'Redirect created.'], 201);
    }

    public function redirectUpdate(Request $request, int $id): JsonResponse
    {
        $redirect = SeoRedirect::findOrFail($id);

        $validated = $request->validate([
            'from_path' => 'sometimes|required|string|max:2048',
            'to_url' => 'sometimes|required|string|max:2048',
            'type' => 'sometimes|required|integer|in:301,302',
            'is_active' => 'sometimes|boolean',
            'note' => 'nullable|string|max:500',
        ]);

        if (isset($validated['from_path'])) {
            $validated['from_path'] = '/' . ltrim($validated['from_path'], '/');
        }

        // Clear old path cache
        $this->clearRedirectCache($redirect->from_path);

        $redirect->update($validated);

        // Clear new path cache
        if (isset($validated['from_path'])) {
            $this->clearRedirectCache($validated['from_path']);
        }

        return response()->json(['data' => $redirect, 'message' => 'Redirect updated.']);
    }

    public function redirectDestroy(int $id): JsonResponse
    {
        $redirect = SeoRedirect::findOrFail($id);
        $this->clearRedirectCache($redirect->from_path);
        $redirect->delete();

        return response()->json(['message' => 'Redirect deleted.']);
    }

    // ─── 404 Monitor ──────────────────────────────────────────────

    public function notFoundIndex(Request $request): JsonResponse
    {
        $query = Seo404Log::query()->orderByDesc('hits');

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where('path', 'like', $search);
        }

        return response()->json([
            'data' => $query->paginate($request->integer('per_page', 25)),
        ]);
    }

    public function notFoundDestroy(int $id): JsonResponse
    {
        Seo404Log::findOrFail($id)->delete();

        return response()->json(['message' => '404 log entry deleted.']);
    }

    public function notFoundClear(): JsonResponse
    {
        Seo404Log::query()->truncate();

        return response()->json(['message' => 'All 404 logs cleared.']);
    }

    public function notFoundToRedirect(Request $request, int $id): JsonResponse
    {
        $log = Seo404Log::findOrFail($id);

        $validated = $request->validate([
            'to_url' => 'required|string|max:2048',
            'type' => 'integer|in:301,302',
        ]);

        $redirect = SeoRedirect::create([
            'from_path' => $log->path,
            'to_url' => $validated['to_url'],
            'type' => $validated['type'] ?? 301,
            'is_active' => true,
            'note' => 'Created from 404 monitor',
        ]);

        $log->delete();
        $this->clearRedirectCache($redirect->from_path);

        return response()->json(['data' => $redirect, 'message' => 'Redirect created from 404 log.'], 201);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    protected function clearRedirectCache(string $path): void
    {
        Cache::forget('mtoptimize_redirect_' . md5($path));
    }
}
