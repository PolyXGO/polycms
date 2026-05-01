<?php

declare(strict_types=1);

namespace Modules\Polyx\Backup\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class BackupCloudAccount extends Model
{
    protected $table = 'backup_cloud_accounts';

    protected $fillable = [
        'name',
        'provider',
        'client_id',
        'client_secret',
        'access_token',
        'refresh_token',
        'expires_at',
        'base_path',
        'base_path_name',
        'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'client_secret',
        'access_token',
        'refresh_token',
    ];

    // --- Provider constants ---
    public const PROVIDER_GOOGLE_DRIVE = 'google_drive';
    public const PROVIDER_ONEDRIVE     = 'onedrive';

    // --- Encrypted attribute accessors (matching RemoteManager CloudStorage pattern) ---

    public function setClientSecretAttribute(?string $value): void
    {
        $this->attributes['client_secret'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getClientSecretAttribute(?string $value): ?string
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function setAccessTokenAttribute(?string $value): void
    {
        $this->attributes['access_token'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getAccessTokenAttribute(?string $value): ?string
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function setRefreshTokenAttribute(?string $value): void
    {
        $this->attributes['refresh_token'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getRefreshTokenAttribute(?string $value): ?string
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    // --- Helpers ---

    public function isTokenExpired(): bool
    {
        if (!$this->expires_at) {
            return true;
        }

        return $this->expires_at->isPast();
    }

    public function isGoogleDrive(): bool
    {
        return $this->provider === self::PROVIDER_GOOGLE_DRIVE;
    }

    public function isOneDrive(): bool
    {
        return $this->provider === self::PROVIDER_ONEDRIVE;
    }

    // --- Scopes ---

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }
}
