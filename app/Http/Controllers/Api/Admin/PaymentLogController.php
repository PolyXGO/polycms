<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\PaymentLog;
use Illuminate\Http\Request;

class PaymentLogController extends Controller
{
    /**
     * List all payment logs with filtering and pagination
     */
    public function index(Request $request)
    {
        $query = PaymentLog::orderBy('created_at', 'desc');

        // Filter by level
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        // Filter by gateway
        if ($request->filled('gateway')) {
            $query->where('gateway', $request->gateway);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('message', 'like', '%' . $request->search . '%');
        }

        $logs = $query->paginate($request->get('per_page', 20));

        return response()->json($logs);
    }

    /**
     * Clear old logs (older than 30 days)
     */
    public function clear(Request $request)
    {
        $days = $request->get('days', 30);
        
        $deleted = PaymentLog::where('created_at', '<', now()->subDays($days))->delete();

        return response()->json([
            'message' => "Deleted {$deleted} old log entries",
            'deleted' => $deleted
        ]);
    }
}
