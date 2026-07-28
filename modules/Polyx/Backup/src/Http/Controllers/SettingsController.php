<?php

declare(strict_types=1);

namespace Modules\Polyx\Backup\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Polyx\Backup\Models\BackupCloudAccount;
use Modules\Polyx\Backup\Drivers\Storage\GoogleDriveStorageDriver;
use Modules\Polyx\Backup\Drivers\Storage\OneDriveStorageDriver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Get backup settings.
     */
    public function index(): JsonResponse
    {
        $settings = \App\Models\Setting::where('key', 'like', 'backup_%')->pluck('value', 'key');

        $cloudAccounts = BackupCloudAccount::all()->map(fn ($a) => [
            'id' => $a->id,
            'name' => $a->name,
            'provider' => $a->provider,
            'is_active' => $a->is_active,
            'base_path' => $a->base_path,
            'base_path_name' => $a->base_path_name,
            'expires_at' => $a->expires_at?->toIso8601String(),
            'is_expired' => $a->isTokenExpired(),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'settings' => $settings,
                'cloud_accounts' => $cloudAccounts,
            ],
        ]);
    }

    /**
     * Update backup settings.
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($request->get('settings', []) as $key => $value) {
            \App\Models\Setting::updateOrCreate(
                ['key' => 'backup_' . $key],
                ['value' => is_array($value) ? json_encode($value) : (string) $value]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
        ]);
    }

    // ─────────────────────────────────────
    // Cloud Accounts
    // ─────────────────────────────────────

    /**
     * List cloud accounts.
     */
    public function cloudAccounts(): JsonResponse
    {
        $accounts = BackupCloudAccount::all()->map(fn ($a) => [
            'id' => $a->id,
            'name' => $a->name,
            'provider' => $a->provider,
            'client_id' => $a->client_id,
            'is_active' => $a->is_active,
            'base_path' => $a->base_path,
            'base_path_name' => $a->base_path_name,
            'expires_at' => $a->expires_at?->toIso8601String(),
            'is_expired' => $a->isTokenExpired(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $accounts,
        ]);
    }

    /**
     * Create or update a cloud account.
     */
    public function storeCloudAccount(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'required|in:google_drive,onedrive',
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
        ]);

        $account = BackupCloudAccount::updateOrCreate(
            ['id' => $request->get('id')],
            [
                'name' => $request->get('name'),
                'provider' => $request->get('provider'),
                'client_id' => $request->get('client_id'),
                'client_secret' => $request->get('client_secret'),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Cloud account saved',
            'data' => [
                'id' => $account->id,
                'name' => $account->name,
                'provider' => $account->provider,
            ],
        ]);
    }

    /**
     * Delete a cloud account.
     */
    public function destroyCloudAccount(int $id): JsonResponse
    {
        $account = BackupCloudAccount::findOrFail($id);
        $account->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cloud account deleted',
        ]);
    }

    /**
     * Get OAuth authorization URL.
     */
    public function authUrl(int $id): JsonResponse
    {
        $account = BackupCloudAccount::findOrFail($id);
        $driver = $this->resolveDriver($account);

        return response()->json([
            'success' => true,
            'data' => ['url' => $driver->getAuthUrl()],
        ]);
    }

    /**
     * Handle OAuth callback.
     */
    public function callback(Request $request, int $id)
    {
        $account = BackupCloudAccount::findOrFail($id);
        $code = $request->get('code');

        if (!$code) {
            return redirect('/admin/backup/settings')->with('error', 'OAuth authorization failed');
        }

        $driver = $this->resolveDriver($account);
        $success = $driver->processCallback($code);

        if ($success) {
            return redirect('/admin/backup/settings')->with('success', 'Cloud account connected successfully');
        }

        return redirect('/admin/backup/settings')->with('error', 'Failed to connect cloud account');
    }

    /**
     * List folders on cloud for folder picker.
     */
    public function listFolders(Request $request, int $id): JsonResponse
    {
        $account = BackupCloudAccount::findOrFail($id);
        $driver = $this->resolveDriver($account);

        $parentId = $request->get('parent_id');
        $result = $driver->listFolders($parentId);

        return response()->json($result);
    }

    /**
     * Update cloud account base path.
     */
    public function updateBasePath(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'base_path' => 'required|string',
            'base_path_name' => 'required|string',
        ]);

        $account = BackupCloudAccount::findOrFail($id);
        $account->update([
            'base_path' => $request->get('base_path'),
            'base_path_name' => $request->get('base_path_name'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Base path updated',
        ]);
    }

    private function resolveDriver(BackupCloudAccount $account)
    {
        return match ($account->provider) {
            BackupCloudAccount::PROVIDER_GOOGLE_DRIVE => new GoogleDriveStorageDriver($account),
            BackupCloudAccount::PROVIDER_ONEDRIVE => new OneDriveStorageDriver($account),
            default => throw new \InvalidArgumentException('Unknown provider: ' . $account->provider),
        };
    }
}
