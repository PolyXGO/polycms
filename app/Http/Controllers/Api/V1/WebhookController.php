<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (!$request->user() || !$request->user()->hasRole('admin')) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $webhooks = Webhook::withCount('deliveries')
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $webhooks->items(),
            'meta' => [
                'current_page' => $webhooks->currentPage(),
                'last_page' => $webhooks->lastPage(),
                'per_page' => $webhooks->perPage(),
                'total' => $webhooks->total(),
            ]
        ]);
    }

    public function deliveries(Request $request, Webhook $webhook): JsonResponse
    {
        if (!$request->user() || !$request->user()->hasRole('admin')) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $deliveries = $webhook->deliveries()
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $deliveries->items(),
            'meta' => [
                'current_page' => $deliveries->currentPage(),
                'last_page' => $deliveries->lastPage(),
                'per_page' => $deliveries->perPage(),
                'total' => $deliveries->total(),
            ]
        ]);
    }

    public function toggleStatus(Request $request, Webhook $webhook): JsonResponse
    {
        if (!$request->user() || !$request->user()->hasRole('admin')) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $webhook->is_active = !$webhook->is_active;
        $webhook->save();

        return response()->json([
            'success' => true,
            'data' => $webhook,
            'message' => $webhook->is_active ? 'Webhook has been enabled.' : 'Webhook has been disabled.'
        ]);
    }
    public function store(Request $request): JsonResponse
    {
        if (!$request->user() || !$request->user()->hasRole('admin')) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:2000',
            'events' => 'required|array',
            'events.*' => 'string',
        ]);

        $webhook = new Webhook([
            'name' => $validated['name'],
            'url' => $validated['url'],
            'events' => $validated['events'],
            'secret' => \Illuminate\Support\Str::random(32),
            'is_active' => true,
        ]);
        $webhook->save();

        return response()->json([
            'success' => true,
            'data' => $webhook,
            'message' => 'Webhook created successfully.',
        ]);
    }

    public function show(Request $request, Webhook $webhook): JsonResponse
    {
        if (!$request->user() || !$request->user()->hasRole('admin')) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $webhook,
        ]);
    }

    public function update(Request $request, Webhook $webhook): JsonResponse
    {
        if (!$request->user() || !$request->user()->hasRole('admin')) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'url' => 'sometimes|required|url|max:2000',
            'events' => 'sometimes|required|array',
            'events.*' => 'string',
        ]);

        $webhook->fill($validated);

        if ($request->has('regenerate_secret') && $request->boolean('regenerate_secret')) {
            $webhook->secret = \Illuminate\Support\Str::random(32);
        }

        $webhook->save();

        return response()->json([
            'success' => true,
            'data' => $webhook,
            'message' => 'Webhook updated successfully.',
        ]);
    }

    public function destroy(Request $request, Webhook $webhook): JsonResponse
    {
        if (!$request->user() || !$request->user()->hasRole('admin')) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $webhook->delete();

        return response()->json([
            'success' => true,
            'message' => 'Webhook deleted successfully.',
        ]);
    }

    public function ping(Request $request, Webhook $webhook): JsonResponse
    {
        if (!$request->user() || !$request->user()->hasRole('admin')) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $dummyPayload = [
            'ping' => 'pong',
            'timestamp' => now()->toIso8601String(),
            'message' => 'This is a test webhook payload from PolyCMS.',
        ];

        \App\Jobs\DispatchWebhookJob::dispatch($webhook, 'ping', $dummyPayload);

        return response()->json([
            'success' => true,
            'message' => 'Ping payload dispatched to the webhook URL.',
        ]);
    }

    public function generateToken(Request $request, Webhook $webhook): JsonResponse
    {
        if (!$request->user() || !$request->user()->hasRole('admin')) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        // Flush old tokens
        $webhook->tokens()->delete();

        // Create new token covering all typical web capabilities
        $token = $webhook->createToken($webhook->name . ' Access Token', ['*']);

        return response()->json([
            'success' => true,
            'token' => $token->plainTextToken,
            'message' => 'New API Token generated successfully.',
        ]);
    }
}
