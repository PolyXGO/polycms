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
            'status' => 'required|in:success,failed,pending',
            'transaction_ref' => 'nullable|string|max:100',
            'admin_note' => 'nullable|string|max:1000',
            'proof_of_payment' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120', // Max 5MB
        ]);

        $transaction = UserTransaction::findOrFail($id);
        $oldStatus = $transaction->status;
        
        $transaction->status = $request->status;

        if ($request->filled('transaction_ref')) {
            $transaction->transaction_ref = $request->transaction_ref;
        }

        $payload = is_array($transaction->payload) ? $transaction->payload : [];
        if ($request->filled('admin_note')) {
            $payload['admin_note'] = $request->admin_note;
        }

        if ($request->hasFile('proof_of_payment')) {
            $path = $request->file('proof_of_payment')->store('private/transactions');
            $payload['proof_of_payment'] = $path;
        }

        $transaction->payload = empty($payload) ? null : $payload;
        $transaction->save();

        if ($request->status === 'success' && $oldStatus !== 'success' && $transaction->order_id) {
            $order = \App\Models\Ecommerce\Order::find($transaction->order_id);
            if ($order) {
                if ($order->payment_status !== 'paid') {
                    $order->update(['payment_status' => 'paid']);
                }
                
                // Automatically move to processing if currently pending
                if ($order->status === 'pending') {
                    $order->update(['status' => 'processing']);
                    \App\Facades\Hook::doAction('order_status_updated', $order, 'pending', 'processing');
                }

                // Add order note for audit trail
                $noteContent = "Transaction #{$transaction->id} marked as Success by Admin.";
                if ($request->filled('transaction_ref')) {
                    $noteContent .= " Ref: {$request->transaction_ref}.";
                }
                
                \App\Models\Ecommerce\OrderNote::create([
                    'order_id' => $order->id,
                    'user_id' => $request->user()?->id,
                    'type' => 'system',
                    'content' => $noteContent,
                    'metadata' => [
                        'transaction_id' => $transaction->id,
                        'proof_of_payment' => $payload['proof_of_payment'] ?? null,
                    ],
                    'is_customer_visible' => false,
                ]);
            }
        }

        return response()->json([
            'message' => 'Transaction status updated successfully',
            'data' => $transaction
        ]);
    }

    /**
     * Serve the proof of payment file
     */
    public function serveProof($id)
    {
        $transaction = UserTransaction::findOrFail($id);
        
        $payload = $transaction->payload;
        if (!is_array($payload) || empty($payload['proof_of_payment'])) {
            abort(404, 'Proof of payment not found.');
        }

        $path = $payload['proof_of_payment'];
        if (!\Illuminate\Support\Facades\Storage::exists($path)) {
            abort(404, 'File not found on disk.');
        }

        return \Illuminate\Support\Facades\Storage::response($path);
    }
}
