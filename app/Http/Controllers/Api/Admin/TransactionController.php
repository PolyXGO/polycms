<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\UserTransaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * List all transactions with filtering and pagination
     */
    public function index(Request $request)
    {
        $query = UserTransaction::with(['user', 'order'])
            ->orderBy('created_at', 'desc');

        // Filter by gateway
        if ($request->filled('gateway')) {
            $query->where('gateway', $request->gateway);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by transaction ref
        if ($request->filled('search')) {
            $query->where('transaction_ref', 'like', '%' . $request->search . '%');
        }

        $transactions = $query->paginate($request->get('per_page', 15));

        return response()->json($transactions);
    }

    /**
     * Get transaction details
     */
    public function show($id)
    {
        $transaction = UserTransaction::with(['user', 'order'])
            ->findOrFail($id);

        return response()->json([
            'data' => $transaction
        ]);
    }

    /**
     * Get transaction statistics
     */
    public function stats()
    {
        $stats = [
            'total' => UserTransaction::count(),
            'success' => UserTransaction::where('status', 'success')->count(),
            'pending' => UserTransaction::where('status', 'pending')->count(),
            'failed' => UserTransaction::where('status', 'failed')->count(),
            'total_amount' => UserTransaction::where('status', 'success')->sum('amount'),
        ];

        return response()->json($stats);
    }

    /**
     * Manually update the transaction status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:success,failed,pending'
        ]);

        $transaction = UserTransaction::findOrFail($id);
        
        $transaction->status = $request->status;
        $transaction->save();

        return response()->json([
            'message' => 'Transaction status updated successfully',
            'data' => $transaction
        ]);
    }
}
