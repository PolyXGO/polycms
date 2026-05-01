<?php

declare(strict_types=1);

namespace Modules\Polyx\Backup\Drivers\Storage;

use Modules\Polyx\Backup\Contracts\StorageDriverInterface;
use Modules\Polyx\Backup\Models\BackupCloudAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Google Drive storage driver for backup files.
 * Follows the same OAuth2 patterns as RemoteManager CloudStorageService.
 */
class GoogleDriveStorageDriver implements StorageDriverInterface
{
    private BackupCloudAccount $account;

    public function __construct(BackupCloudAccount $account)
    {
        $this->account = $account;
    }

    public function getDriverName(): string
    {
        return 'google_drive';
    }

    public function getAuthUrl(): string
    {
        $params = http_build_query([
            'client_id' => $this->account->client_id,
            'redirect_uri' => $this->getRedirectUri(),
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/drive.file',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $this->account->id,
        ]);

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . $params;
    }

    public function processCallback(string $code): bool
    {
        try {
            $response = Http::post('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => $this->account->client_id,
                'client_secret' => $this->account->client_secret,
                'redirect_uri' => $this->getRedirectUri(),
                'grant_type' => 'authorization_code',
            ]);

            if (!$response->successful()) {
                Log::error('Backup: Google Drive OAuth failed', ['response' => $response->body()]);
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
            Log::error('Backup: Google Drive OAuth exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function upload(string $localPath, string $remotePath): array
    {
        try {
            $this->refreshTokenIfNeeded();
            $folderId = $this->account->base_path ?: 'root';
            $filename = basename($remotePath);
            $fileSize = filesize($localPath);

            // Use resumable upload for files > 5MB
            if ($fileSize > 5 * 1024 * 1024) {
                return $this->resumableUpload($localPath, $filename, $folderId);
            }

            // Simple upload for small files
            $metadata = json_encode([
                'name' => $filename,
                'parents' => [$folderId],
            ]);

            $boundary = 'backup_boundary_' . uniqid();
            $body = "--{$boundary}\r\n"
                . "Content-Type: application/json; charset=UTF-8\r\n\r\n"
                . $metadata . "\r\n"
                . "--{$boundary}\r\n"
                . "Content-Type: application/octet-stream\r\n\r\n"
                . file_get_contents($localPath) . "\r\n"
                . "--{$boundary}--";

            $response = Http::withToken($this->account->access_token)
                ->withHeaders([
                    'Content-Type' => "multipart/related; boundary={$boundary}",
                ])
                ->withBody($body, "multipart/related; boundary={$boundary}")
                ->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');

            if (!$response->successful()) {
                throw new \RuntimeException('Upload failed: ' . $response->body());
            }

            return ['success' => true, 'remotePath' => $response->json('id')];
        } catch (\Exception $e) {
            Log::error('Backup: Google Drive upload failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function download(string $remotePath, string $localPath): array
    {
        try {
            $this->refreshTokenIfNeeded();

            // remotePath = file ID on Google Drive
            $response = Http::withToken($this->account->access_token)
                ->withOptions(['sink' => $localPath])
                ->get("https://www.googleapis.com/drive/v3/files/{$remotePath}?alt=media");

            if (!$response->successful()) {
                throw new \RuntimeException('Download failed: HTTP ' . $response->status());
            }

            return ['success' => true, 'localPath' => $localPath];
        } catch (\Exception $e) {
            Log::error('Backup: Google Drive download failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function listBackups(string $remotePath = ''): array
    {
        try {
            $this->refreshTokenIfNeeded();
            $folderId = $remotePath ?: ($this->account->base_path ?: 'root');

            $response = Http::withToken($this->account->access_token)
                ->get('https://www.googleapis.com/drive/v3/files', [
                    'q' => "'{$folderId}' in parents and mimeType != 'application/vnd.google-apps.folder' and trashed = false",
                    'fields' => 'files(id,name,size,modifiedTime,mimeType)',
                    'orderBy' => 'modifiedTime desc',
                    'pageSize' => 100,
                ]);

            if (!$response->successful()) {
                throw new \RuntimeException('List failed: ' . $response->body());
            }

            $files = collect($response->json('files', []))->map(fn ($f) => [
                'id' => $f['id'],
                'name' => $f['name'],
                'size' => (int) ($f['size'] ?? 0),
                'modified_at' => $f['modifiedTime'] ?? null,
            ])->all();

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
                ->delete("https://www.googleapis.com/drive/v3/files/{$remotePath}");

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
            $folderId = $parentId ?: 'root';

            $response = Http::withToken($this->account->access_token)
                ->get('https://www.googleapis.com/drive/v3/files', [
                    'q' => "'{$folderId}' in parents and mimeType = 'application/vnd.google-apps.folder' and trashed = false",
                    'fields' => 'files(id,name)',
                    'orderBy' => 'name',
                    'pageSize' => 100,
                ]);

            if (!$response->successful()) {
                throw new \RuntimeException('Folder list failed: ' . $response->body());
            }

            return ['success' => true, 'folders' => $response->json('files', [])];
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

        $response = Http::post('https://oauth2.googleapis.com/token', [
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
            'expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
        ]);
    }

    private function resumableUpload(string $localPath, string $filename, string $folderId): array
    {
        // Step 1: Initiate resumable upload session
        $response = Http::withToken($this->account->access_token)
            ->withHeaders(['Content-Type' => 'application/json; charset=UTF-8'])
            ->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=resumable', [
                'name' => $filename,
                'parents' => [$folderId],
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Resumable upload init failed: ' . $response->body());
        }

        $uploadUri = $response->header('Location');
        if (!$uploadUri) {
            throw new \RuntimeException('No upload URI returned');
        }

        // Step 2: Upload in chunks
        $chunkSize = 5 * 1024 * 1024; // 5MB chunks
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
              ->put($uploadUri);

            if ($chunkResponse->status() === 200 || $chunkResponse->status() === 201) {
                fclose($handle);
                return ['success' => true, 'remotePath' => $chunkResponse->json('id')];
            }

            if ($chunkResponse->status() !== 308) {
                fclose($handle);
                throw new \RuntimeException('Chunk upload failed: HTTP ' . $chunkResponse->status());
            }

            $offset += $chunkLength;
        }

        fclose($handle);
        throw new \RuntimeException('Upload completed but no final response received');
    }

    private function getRedirectUri(): string
    {
        return url('/api/v1/backup/cloud-accounts/' . $this->account->id . '/callback');
    }
}
