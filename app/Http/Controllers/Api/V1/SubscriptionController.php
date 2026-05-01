<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of subscriptions.
     */
    public function index(Request $request): JsonResponse
    {
        $query = UserSubscription::with(['user', 'service', 'product']);

        if ($request->has('user_id')) {
            if ($request->user_id === 'me') {
                $query->where('user_id', $request->user()->id);
            } else {
                $query->where('user_id', $request->user_id);
            }
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $subscriptions = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($subscriptions);
    }

    /**
     * Display the specified subscription.
     */
    public function show($id): JsonResponse
    {
        $subscription = UserSubscription::with(['user', 'service', 'product'])->findOrFail($id);
        
        // Basic authorization check: Users can only view their own subscriptions unless they are admin (skipped for now, assuming admin middleware/logic handles role checks, but for safety in 'me' context)
        if (request()->has('user_id') && request()->user_id === 'me') {
            if ($subscription->user_id !== request()->user()->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        return response()->json($subscription);
    }
}
