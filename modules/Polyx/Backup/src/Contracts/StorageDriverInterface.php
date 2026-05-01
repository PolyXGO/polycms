<?php

declare(strict_types=1);

namespace Modules\Polyx\Backup\Contracts;

interface StorageDriverInterface
{
    /**
     * Upload a local file to the storage destination.
     *
     * @return array{success: bool, remotePath?: string, error?: string}
     */
    public function upload(string $localPath, string $remotePath): array;

    /**
     * Download a file from storage to local path.
     *
     * @return array{success: bool, localPath?: string, error?: string}
     */
    public function download(string $remotePath, string $localPath): array;

    /**
     * List backup files at a given path.
     *
     * @return array{success: bool, files?: array, error?: string}
     */
    public function listBackups(string $remotePath = ''): array;

    /**
     * Delete a file from storage.
     *
     * @return array{success: bool, error?: string}
     */
    public function delete(string $remotePath): array;

    /**
     * List folders at a given parent path/ID.
     *
     * @return array{success: bool, folders?: array, error?: string}
     */
    public function listFolders(?string $parentId = null): array;

    /**
     * Get the OAuth authorization URL for connecting cloud accounts.
     */
    public function getAuthUrl(): string;

    /**
     * Process the OAuth callback code to obtain tokens.
     */
    public function processCallback(string $code): bool;

    /**
     * Get the driver identifier.
     */
    public function getDriverName(): string;
}
