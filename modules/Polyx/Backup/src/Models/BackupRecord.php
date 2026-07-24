<?php

declare(strict_types=1);

namespace Modules\Polyx\Backup\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupRecord extends Model
{
    protected $table = 'backup_records';

    protected $fillable = [
        'name',
        'type',
        'status',
        'filename',
        'disk',
        'remote_path',
        'size',
        'database_size',
        'checksum',
        'manifest',
        'is_scheduled',
        'is_encrypted',
        'error_message',
        'started_at',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'manifest' => 'array',
        'is_scheduled' => 'boolean',
        'is_encrypted' => 'boolean',
        'size' => 'integer',
        'database_size' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // --- Status constants ---
    public const STATUS_PENDING    = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED  = 'completed';
    public const STATUS_FAILED     = 'failed';
    public const STATUS_RESTORING  = 'restoring';

    // --- Type constants ---
    public const TYPE_FULL     = 'full';
    public const TYPE_DATABASE = 'database';
    public const TYPE_FILES    = 'files';

    // --- Disk constants ---
    public const DISK_LOCAL        = 'local';
    public const DISK_GOOGLE_DRIVE = 'google_drive';
    public const DISK_ONEDRIVE     = 'onedrive';

    // --- Relationships ---

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // --- Scopes ---

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByDisk($query, string $disk)
    {
        return $query->where('disk', $disk);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // --- Helpers ---

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isProcessing(): bool
    {
        return in_array($this->status, [self::STATUS_PROCESSING, self::STATUS_RESTORING]);
    }

    public function getSizeFormattedAttribute(): string
    {
        return self::formatBytes($this->size);
    }

    public function getDatabaseSizeFormattedAttribute(): string
    {
        return self::formatBytes($this->database_size);
    }

    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
