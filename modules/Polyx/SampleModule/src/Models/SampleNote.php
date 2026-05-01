<?php

declare(strict_types=1);

namespace Modules\Polyx\SampleModule\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * SampleNote — CRUD Demo Model
 *
 * This model demonstrates the standard PolyCMS model pattern:
 * - Extends Eloquent Model
 * - Uses $fillable for mass-assignment protection
 * - Uses $casts for type casting
 * - Defines relationships, scopes, and accessors
 *
 * TABLE: sample_notes
 * ┌────────────┬──────────────┬───────────────────────────────┐
 * │ Column     │ Type         │ Description                   │
 * ├────────────┼──────────────┼───────────────────────────────┤
 * │ id         │ bigint (PK)  │ Auto-increment primary key    │
 * │ title      │ varchar(255) │ Note title                    │
 * │ content    │ text         │ Note content (nullable)       │
 * │ color      │ varchar(20)  │ Color label (default: blue)   │
 * │ is_pinned  │ boolean      │ Pin note to top               │
 * │ user_id    │ bigint (FK)  │ Creator reference             │
 * │ created_at │ timestamp    │ Auto-managed by Eloquent      │
 * │ updated_at │ timestamp    │ Auto-managed by Eloquent      │
 * └────────────┴──────────────┴───────────────────────────────┘
 */
class SampleNote extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Convention: module prefix + plural noun (e.g., sample_notes)
     */
    protected $table = 'sample_notes';

    /**
     * Mass-assignable attributes.
     * IMPORTANT: Only list fields that users can set via forms/API.
     * Never include 'id', 'created_at', 'updated_at'.
     */
    protected $fillable = [
        'title',
        'content',
        'color',
        'is_pinned',
        'user_id',
    ];

    /**
     * Attribute type casting.
     * Eloquent will automatically cast these when reading from DB.
     */
    protected $casts = [
        'is_pinned'  => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Default attribute values for new instances.
     */
    protected $attributes = [
        'color'     => 'blue',
        'is_pinned' => false,
    ];

    // ─── Relationships ─────────────────────────────────────────

    /**
     * The user who created this note.
     * Convention: belongsTo for FK relationships.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // ─── Scopes ────────────────────────────────────────────────

    /**
     * Query scope: only pinned notes.
     * Usage: SampleNote::pinned()->get()
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Query scope: search by title or content.
     * Usage: SampleNote::search('keyword')->get()
     */
    public function scopeSearch($query, string $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('title', 'ILIKE', "%{$keyword}%")
              ->orWhere('content', 'ILIKE', "%{$keyword}%");
        });
    }

    // ─── Accessors ─────────────────────────────────────────────

    /**
     * Get a short excerpt of the content.
     * Usage: $note->excerpt
     */
    public function getExcerptAttribute(): string
    {
        $content = strip_tags($this->content ?? '');
        return strlen($content) > 100
            ? substr($content, 0, 100) . '...'
            : $content;
    }

    // ─── Available Colors ──────────────────────────────────────

    /**
     * Get the list of available note colors.
     * Used by the frontend form for color selection.
     */
    public static function availableColors(): array
    {
        return ['blue', 'green', 'yellow', 'red', 'purple', 'gray'];
    }
}
