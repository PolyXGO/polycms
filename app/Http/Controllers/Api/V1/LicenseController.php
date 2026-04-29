<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\ProductLicense;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LicenseController extends Controller
{
    /**
     * Display a listing of licenses.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ProductLicense::with(['subscription.user', 'subscription.product']);

        if ($request->has('user_id')) {
            if ($request->user_id === 'me') {
                $userId = $request->user()->id;
                $query->whereHas('subscription', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                });
            } else {
                // If filtering by specific user ID (admin use case usually)
                $targetUserId = $request->user_id;
                $query->whereHas('subscription', function($q) use ($targetUserId) {
                    $q->where('user_id', $targetUserId);
                });
            }
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $licenses = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($licenses);
    }

    /**
     * Display the specified license.
     */
    public function show($id): JsonResponse
    {
        $license = ProductLicense::with(['subscription.user', 'subscription.product', 'activations'])->findOrFail($id);
        
        if (request()->has('user_id') && request()->user_id === 'me') {
            if ($license->subscription->user_id !== request()->user()->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        return response()->json($license);
    }
}
