<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemUpdateLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_version',
        'to_version',
        'status',
        'backup_path',
        'steps',
        'error_message',
        'started_at',
        'completed_at',
        'performed_by',
    ];

    protected $casts = [
        'steps' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_ROLLED_BACK = 'rolled_back';

    /**
     * Get the user who performed the update.
     */
    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Add a step entry to the log.
     */
    public function addStep(string $step, string $status, string $message = ''): void
    {
        $steps = $this->steps ?? [];
        $steps[] = [
            'step' => $step,
            'status' => $status,
            'message' => $message,
            'timestamp' => now()->toISOString(),
        ];
        $this->steps = $steps;
        $this->save();
    }

    /**
     * Scope to get latest update.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
