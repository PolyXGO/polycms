<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ContentVote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentVoteController extends Controller
{
    /**
     * Store a vote (public, rate-limited by IP)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'voteable_type' => 'required|string|in:post,page,product',
            'voteable_id'   => 'required|integer|min:1',
            'type'          => 'required|string|max:30',
            'source'        => 'nullable|string|max:50',
        ]);

        $ip = $request->ip();

        // Check if this IP already voted on this content
        $existing = ContentVote::where('voteable_type', $validated['voteable_type'])
            ->where('voteable_id', $validated['voteable_id'])
            ->where('ip_address', $ip)
            ->first();

        if ($existing) {
            // Update the vote type if changed
            $existing->update([
                'type'   => $validated['type'],
                'source' => $validated['source'] ?? $existing->source,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vote updated',
                'data'    => ['id' => $existing->id, 'type' => $existing->type],
            ]);
        }

        $vote = ContentVote::create([
            'voteable_type' => $validated['voteable_type'],
            'voteable_id'   => $validated['voteable_id'],
            'type'          => $validated['type'],
            'ip_address'    => $ip,
            'user_id'       => $request->user()?->id,
            'source'        => $validated['source'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vote recorded',
            'data'    => ['id' => $vote->id, 'type' => $vote->type],
        ], 201);
    }

    /**
     * Get vote stats for admin reports
     */
    public function stats(Request $request): JsonResponse
    {
        $query = ContentVote::query();

        // Filter by voteable_type
        if ($request->has('voteable_type')) {
            $query->where('voteable_type', $request->get('voteable_type'));
        }

        // Filter by source
        if ($request->has('source')) {
            $query->where('source', $request->get('source'));
        }

        // Filter by date range
        if ($request->has('from')) {
            $query->where('created_at', '>=', $request->get('from'));
        }
        if ($request->has('to')) {
            $query->where('created_at', '<=', $request->get('to'));
        }

        // Summary stats
        $total = (clone $query)->count();
        $byType = (clone $query)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        // Daily trend (last 30 days)
        $trend = (clone $query)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, type, COUNT(*) as count')
            ->groupBy('date', 'type')
            ->orderBy('date')
            ->get();

        // Per-content breakdown (top 20)
        $perContent = (clone $query)
            ->selectRaw('voteable_type, voteable_id, type, COUNT(*) as count')
            ->groupBy('voteable_type', 'voteable_id', 'type')
            ->orderByDesc('count')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'total'       => $total,
                'by_type'     => $byType,
                'trend'       => $trend,
                'per_content' => $perContent,
            ],
        ]);
    }

    /**
     * List all votes (admin, paginated)
     */
    public function index(Request $request): JsonResponse
    {
        $query = ContentVote::query()->latest();

        if ($request->has('voteable_type')) {
            $query->where('voteable_type', $request->get('voteable_type'));
        }
        if ($request->has('source')) {
            $query->where('source', $request->get('source'));
        }
        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        $votes = $query->paginate($request->get('per_page', 20));

        // Eager load voteable titles
        $votes->getCollection()->transform(function ($vote) {
            $modelClass = match ($vote->voteable_type) {
                'post'    => \App\Models\Post::class,
                'page'    => \App\Models\Post::class,
                'product' => class_exists(\App\Models\Product::class) ? \App\Models\Product::class : null,
                default   => null,
            };

            if ($modelClass) {
                $content = $modelClass::find($vote->voteable_id);
                $vote->content_title = $content?->title ?? $content?->name ?? 'Unknown';
                $vote->content_url = $content?->frontend_url ?? null;
            }

            return $vote;
        });

        return response()->json([
            'success' => true,
            'data'    => $votes,
        ]);
    }
}
