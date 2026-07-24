<?php

use Illuminate\Support\Facades\Route;
use Modules\Polyx\Backup\Http\Controllers\BackupController;
use Modules\Polyx\Backup\Http\Controllers\SettingsController;

Route::prefix('backup')->middleware(['auth:sanctum'])->group(function () {

    // System info
    Route::get('info', [BackupController::class, 'info']);

    // Backup CRUD
    Route::get('records', [BackupController::class, 'index']);
    Route::post('create', [BackupController::class, 'create']);
    Route::post('{id}/restore', [BackupController::class, 'restore']);
    Route::delete('{id}', [BackupController::class, 'destroy']);
    Route::get('{id}/download-url', [BackupController::class, 'downloadUrl']);
    Route::get('{id}/download', [BackupController::class, 'download'])
        ->withoutMiddleware(['auth:sanctum'])
        ->middleware('signed')
        ->name('backup.api.download');
    Route::get('{id}/status', [BackupController::class, 'status']);

    // Settings
    Route::get('settings', [SettingsController::class, 'index']);
    Route::put('settings', [SettingsController::class, 'update']);

    // Cloud accounts
    Route::get('cloud-accounts', [SettingsController::class, 'cloudAccounts']);
    Route::post('cloud-accounts', [SettingsController::class, 'storeCloudAccount']);
    Route::delete('cloud-accounts/{id}', [SettingsController::class, 'destroyCloudAccount']);
    Route::get('cloud-accounts/{id}/auth-url', [SettingsController::class, 'authUrl']);
    Route::get('cloud-accounts/{id}/folders', [SettingsController::class, 'listFolders']);
    Route::put('cloud-accounts/{id}/base-path', [SettingsController::class, 'updateBasePath']);
});

// OAuth callback (no auth middleware — redirected from external provider)
Route::get('backup/cloud-accounts/{id}/callback', [SettingsController::class, 'callback'])
    ->name('backup.cloud.callback');
