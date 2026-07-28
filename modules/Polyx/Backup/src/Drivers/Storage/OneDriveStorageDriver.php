<?php

declare(strict_types=1);

namespace Modules\Polyx\Backup\Drivers\Storage;

use Modules\Polyx\Backup\Contracts\StorageDriverInterface;
use Modules\Polyx\Backup\Models\BackupCloudAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OneDrive storage driver for backup files.
 * Follows RemoteManager CloudStorageService OAuth2 patterns.
 */
class OneDriveStorageDriver implements StorageDriverInterface
{
    private BackupCloudAccount $account;

    public function __construct(BackupCloudAccount $account)
    {
        $this->account = $account;
    }

    public function getDriverName(): string
    {
        return 'onedrive';
    }

    public function getAuthUrl(): string
    {
        $params = http_build_query([
            'client_id' => $this->account->client_id,
            'redirect_uri' => $this->getRedirectUri(),
            'response_type' => 'code',
            'scope' => 'Files.ReadWrite.All offline_access',
            'state' => $this->account->id,
        ]);

        return 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize?' . $params;
    }

    public function processCallback(string $code): bool
    {
        try {
            $response = Http::asForm()->post('https://login.microsoftonline.com/common/oauth2/v2.0/token', [
                'code' => $code,
                'client_id' => $this->account->client_id,
                'client_secret' => $this->account->client_secret,
                'redirect_uri' => $this->getRedirectUri(),
                'grant_type' => 'authorization_code',
            ]);

            if (!$response->successful()) {
                Log::error('Backup: OneDrive OAuth failed', ['response' => $response->body()]);
                return false;
            }

            $data = $response->json();
            $this->account->update([
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'] ?? $this->account->refresh_token,
                'expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
                'is_active' => true,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Backup: OneDrive OAuth exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function upload(string $localPath, string $remotePath): array
    {
        try {
            $this->refreshTokenIfNeeded();
            $basePath = $this->account->base_path ?: '';
            $fullPath = $basePath ? rtrim($basePath, '/') . '/' . basename($remotePath) : basename($remotePath);
            $fileSize = filesize($localPath);

            // Use upload session for files > 4MB
            if ($fileSize > 4 * 1024 * 1024) {
                return $this->largeFileUpload($localPath, $fullPath);
            }

            // Simple upload for small files
            $content = file_get_contents($localPath);

            $response = Http::withToken($this->account->access_token)
                ->withBody($content, 'application/octet-stream')
                ->put("https://graph.microsoft.com/v1.0/me/drive/root:/{$fullPath}:/content");

            if (!$response->successful()) {
                throw new \RuntimeException('Upload failed: ' . $response->body());
            }

            return ['success' => true, 'remotePath' => $response->json('id')];
        } catch (\Exception $e) {
            Log::error('Backup: OneDrive upload failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function download(string $remotePath, string $localPath): array
    {
        try {
            $this->refreshTokenIfNeeded();

            $response = Http::withToken($this->account->access_token)
                ->get("https://graph.microsoft.com/v1.0/me/drive/items/{$remotePath}");

            if (!$response->successful()) {
                throw new \RuntimeException('Get file info failed: HTTP ' . $response->status());
            }

            $downloadUrl = $response->json('@microsoft.graph.downloadUrl');
            if (!$downloadUrl) {
                throw new \RuntimeException('No download URL available');
            }

            $downloadResponse = Http::withOptions(['sink' => $localPath])->get($downloadUrl);

            if (!$downloadResponse->successful()) {
                throw new \RuntimeException('Download failed: HTTP ' . $downloadResponse->status());
            }

            return ['success' => true, 'localPath' => $localPath];
        } catch (\Exception $e) {
            Log::error('Backup: OneDrive download failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function listBackups(string $remotePath = ''): array
    {
        try {
            $this->refreshTokenIfNeeded();
            $path = $remotePath ?: ($this->account->base_path ?: '');

            $url = $path
                ? "https://graph.microsoft.com/v1.0/me/drive/root:/{$path}:/children"
                : 'https://graph.microsoft.com/v1.0/me/drive/root/children';

            $response = Http::withToken($this->account->access_token)
                ->get($url, [
                    '$select' => 'id,name,size,lastModifiedDateTime,file',
                    '$orderby' => 'lastModifiedDateTime desc',
                    '$top' => 100,
                ]);

            if (!$response->successful()) {
                throw new \RuntimeException('List failed: ' . $response->body());
            }

            $files = collect($response->json('value', []))
                ->filter(fn ($item) => isset($item['file'])) // Only files, not folders
                ->map(fn ($f) => [
                    'id' => $f['id'],
                    'name' => $f['name'],
                    'size' => (int) ($f['size'] ?? 0),
                    'modified_at' => $f['lastModifiedDateTime'] ?? null,
                ])->values()->all();

            return ['success' => true, 'files' => $files];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function delete(string $remotePath): array
    {
        try {
            $this->refreshTokenIfNeeded();

            $response = Http::withToken($this->account->access_token)
                ->delete("https://graph.microsoft.com/v1.0/me/drive/items/{$remotePath}");

            if (!$response->successful() && $response->status() !== 404) {
                throw new \RuntimeException('Delete failed: HTTP ' . $response->status());
            }

            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function listFolders(?string $parentId = null): array
    {
        try {
            $this->refreshTokenIfNeeded();

            $url = $parentId
                ? "https://graph.microsoft.com/v1.0/me/drive/items/{$parentId}/children"
                : 'https://graph.microsoft.com/v1.0/me/drive/root/children';

            $response = Http::withToken($this->account->access_token)
                ->get($url, [
                    '$select' => 'id,name,folder',
                    '$filter' => 'folder ne null',
                    '$orderby' => 'name',
                    '$top' => 100,
                ]);

            if (!$response->successful()) {
                throw new \RuntimeException('Folder list failed: ' . $response->body());
            }

            $folders = collect($response->json('value', []))
                ->filter(fn ($item) => isset($item['folder']))
                ->map(fn ($f) => ['id' => $f['id'], 'name' => $f['name']])
                ->values()->all();

            return ['success' => true, 'folders' => $folders];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // --- Private helpers ---

    private function refreshTokenIfNeeded(): void
    {
        if (!$this->account->isTokenExpired()) {
            return;
        }

        if (!$this->account->refresh_token) {
            throw new \RuntimeException('Token expired and no refresh token available');
        }

        $response = Http::asForm()->post('https://login.microsoftonline.com/common/oauth2/v2.0/token', [
            'client_id' => $this->account->client_id,
            'client_secret' => $this->account->client_secret,
            'refresh_token' => $this->account->refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Token refresh failed: ' . $response->body());
        }

        $data = $response->json();
        $this->account->update([
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? $this->account->refresh_token,
            'expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
        ]);
    }

    private function largeFileUpload(string $localPath, string $fullPath): array
    {
        // Create upload session
        $response = Http::withToken($this->account->access_token)
            ->post("https://graph.microsoft.com/v1.0/me/drive/root:/{$fullPath}:/createUploadSession", [
                'item' => ['@microsoft.graph.conflictBehavior' => 'replace'],
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Upload session creation failed: ' . $response->body());
        }

        $uploadUrl = $response->json('uploadUrl');
        if (!$uploadUrl) {
            throw new \RuntimeException('No upload URL returned');
        }

        // Upload in chunks (10MB)
        $chunkSize = 10 * 1024 * 1024;
        $handle = fopen($localPath, 'rb');
        $totalSize = filesize($localPath);
        $offset = 0;

        while ($offset < $totalSize) {
            $chunk = fread($handle, $chunkSize);
            $chunkLength = strlen($chunk);
            $endByte = $offset + $chunkLength - 1;

            $chunkResponse = Http::withHeaders([
                'Content-Range' => "bytes {$offset}-{$endByte}/{$totalSize}",
                'Content-Length' => (string) $chunkLength,
            ])->withBody($chunk, 'application/octet-stream')
              ->put($uploadUrl);

            if ($chunkResponse->status() === 200 || $chunkResponse->status() === 201) {
                fclose($handle);
                return ['success' => true, 'remotePath' => $chunkResponse->json('id')];
            }

            if ($chunkResponse->status() !== 202) {
                fclose($handle);
                throw new \RuntimeException('Chunk upload failed: HTTP ' . $chunkResponse->status());
            }

            $offset += $chunkLength;
        }

        fclose($handle);
        throw new \RuntimeException('Upload completed but no final response');
    }

    private function getRedirectUri(): string
    {
        return url('/api/v1/backup/cloud-accounts/' . $this->account->id . '/callback');
    }
}
