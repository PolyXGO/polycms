<?php

declare(strict_types=1);

namespace Modules\Polyx\SampleModule\Controllers\Api\V1;

use App\Facades\Hook;
use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Polyx\SampleModule\Models\SampleNote;

/**
 * Note Controller — Full CRUD API Reference
 *
 * This controller demonstrates the standard PolyCMS CRUD pattern:
 * - RESTful API design (index, store, show, update, destroy)
 * - Input validation with Laravel's validate()
 * - Pagination with configurable page size
 * - Search/filter support
 * - Hook integration (fire events on CRUD actions)
 * - Consistent JSON response format
 *
 * API Endpoints (auto-registered via Route::apiResource):
 * ┌────────┬───────────────────────────────────┬─────────────┐
 * │ Method │ URI                               │ Action      │
 * ├────────┼───────────────────────────────────┼─────────────┤
 * │ GET    │ /api/v1/sample-module/notes       │ index       │
 * │ POST   │ /api/v1/sample-module/notes       │ store       │
 * │ GET    │ /api/v1/sample-module/notes/{id}  │ show        │
 * │ PUT    │ /api/v1/sample-module/notes/{id}  │ update      │
 * │ DELETE │ /api/v1/sample-module/notes/{id}  │ destroy     │
 * └────────┴───────────────────────────────────┴─────────────┘
 */
class NoteController extends Controller
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {}

    /**
     * LIST — Get paginated list of notes.
     *
     * Query params:
     * - ?search=keyword  → filter by title/content
     * - ?pinned=1        → only pinned notes
     * - ?color=blue      → filter by color
     * - ?sort=created_at&order=desc → sorting
     * - ?per_page=10     → pagination size
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $this->settings->get(
            'sample_module_notes_per_page',
            $request->input('per_page', '10')
        );

        $query = SampleNote::with('user');

        // Search filter
        if ($search = $request->input('search')) {
            $query->search($search);
        }

        // Pinned filter
        if ($request->boolean('pinned')) {
            $query->pinned();
        }

        // Color filter
        if ($color = $request->input('color')) {
            $query->where('color', $color);
        }

        // Sorting (default: pinned first, then newest)
        $sort = $request->input('sort', 'created_at');
        $order = $request->input('order', 'desc');
        $allowedSorts = ['title', 'created_at', 'updated_at', 'color', 'is_pinned'];

        if (in_array($sort, $allowedSorts)) {
            $query->orderByDesc('is_pinned')->orderBy($sort, $order);
        } else {
            $query->orderByDesc('is_pinned')->orderByDesc('created_at');
        }

        $notes = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $notes->items(),
            'meta'    => [
                'current_page' => $notes->currentPage(),
                'last_page'    => $notes->lastPage(),
                'per_page'     => $notes->perPage(),
                'total'        => $notes->total(),
            ],
        ]);
    }

    /**
     * CREATE — Store a new note.
     *
     * Demonstrates:
     * - Input validation
     * - Auto-setting user_id from auth
     * - Firing a custom hook after creation
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'     => ['required', 'string', 'max:255'],
            'content'   => ['nullable', 'string', 'max:10000'],
            'color'     => ['sometimes', 'string', 'in:' . implode(',', SampleNote::availableColors())],
            'is_pinned' => ['sometimes', 'boolean'],
        ]);

        $validated['user_id'] = $request->user()->id;

        $note = SampleNote::create($validated);
        $note->load('user');

        // Fire a custom action hook — other modules can listen to this!
        // Example: a notification module could send alerts on new notes
        Hook::doAction('sample_module.note.created', $note);

        return response()->json([
            'success' => true,
            'message' => 'Note created successfully',
            'data'    => $note,
        ], 201);
    }

    /**
     * READ — Get a single note by ID.
     */
    public function show(int $id): JsonResponse
    {
        $note = SampleNote::with('user')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $note,
        ]);
    }

    /**
     * UPDATE — Update an existing note.
     *
     * Uses the same validation as store() but all fields are optional.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $note = SampleNote::findOrFail($id);

        $validated = $request->validate([
            'title'     => ['sometimes', 'required', 'string', 'max:255'],
            'content'   => ['nullable', 'string', 'max:10000'],
            'color'     => ['sometimes', 'string', 'in:' . implode(',', SampleNote::availableColors())],
            'is_pinned' => ['sometimes', 'boolean'],
        ]);

        $note->update($validated);
        $note->load('user');

        Hook::doAction('sample_module.note.updated', $note);

        return response()->json([
            'success' => true,
            'message' => 'Note updated successfully',
            'data'    => $note,
        ]);
    }

    /**
     * DELETE — Remove a note.
     *
     * Fires a hook before deletion so other modules can clean up
     * related data (e.g., comments, attachments).
     */
    public function destroy(int $id): JsonResponse
    {
        $note = SampleNote::findOrFail($id);

        Hook::doAction('sample_module.note.deleting', $note);

        $note->delete();

        Hook::doAction('sample_module.note.deleted', $id);

        return response()->json([
            'success' => true,
            'message' => 'Note deleted successfully',
        ]);
    }
}
