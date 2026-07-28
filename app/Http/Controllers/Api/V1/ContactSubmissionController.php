<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ContactSubmissionController extends Controller
{
    /**
     * Display a listing of submissions.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ContactSubmission::with('form');

        if ($request->filled('form_id')) {
            $query->where('form_id', $request->form_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('data', 'like', "%{$search}%");
            });
        }

        $submissions = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($submissions);
    }

    /**
     * Update the status (read/unread) of a submission.
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $submission = ContactSubmission::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:read,unread',
        ]);

        $submission->update(['status' => $validated['status']]);

        // Invalidate admin menu cache to update badge count
        \Illuminate\Support\Facades\Cache::forget('polycms.admin_menu.version');
        \App\Support\ResilientCache::put('polycms.admin_menu.version', time());

        return response()->json($submission);
    }

    /**
     * Remove the specified submission.
     */
    public function destroy($id): JsonResponse
    {
        $submission = ContactSubmission::findOrFail($id);
        $submission->delete();

        // Invalidate admin menu cache to update badge count
        \Illuminate\Support\Facades\Cache::forget('polycms.admin_menu.version');
        \App\Support\ResilientCache::put('polycms.admin_menu.version', time());

        return response()->json(['success' => true]);
    }

    /**
     * Get dashboard report metrics for contact submissions.
     */
    public function reports(): JsonResponse
    {
        $totalSubmissions = ContactSubmission::count();
        $unreadSubmissions = ContactSubmission::where('status', 'unread')->count();

        // Submissions by type
        $byType = ContactSubmission::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get();

        // Daily submission stats for last 30 days
        $thirtyDaysAgo = now()->subDays(30)->startOfDay();
        $dailyRaw = ContactSubmission::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as count')
        )
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy('date');

        // Fill gaps in daily stats
        $dailyStats = [];
        for ($i = 30; $i >= 0; $i--) {
            $dateStr = now()->subDays($i)->format('Y-m-d');
            $dailyStats[] = [
                'date' => $dateStr,
                'count' => $dailyRaw->has($dateStr) ? (int) $dailyRaw[$dateStr]->count : 0,
            ];
        }

        // Recent unread submissions
        $recentUnread = ContactSubmission::with('form')
            ->where('status', 'unread')
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'total_submissions' => $totalSubmissions,
            'unread_submissions' => $unreadSubmissions,
            'by_type' => $byType,
            'daily_stats' => $dailyStats,
            'recent_unread' => $recentUnread,
        ]);
    }
}
