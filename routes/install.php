<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstallController;

Route::middleware(['web', \App\Http\Middleware\CheckIfInstalled::class])->group(function () {
    Route::get('/install', [InstallController::class, 'index'])->name('install.index');
    Route::get('/install/database', [InstallController::class, 'database'])->name('install.database');
    Route::post('/install/database', [InstallController::class, 'databaseSave'])->name('install.database.save');
    Route::get('/install/migrations', [InstallController::class, 'migrations'])->name('install.migrations');
    Route::post('/install/migrations/run', [InstallController::class, 'migrationsRun'])->name('install.migrations.run');
    Route::get('/install/admin', [InstallController::class, 'admin'])->name('install.admin');
    Route::post('/install/admin', [InstallController::class, 'adminSave'])->name('install.admin.save');
    Route::get('/install/finish', [InstallController::class, 'finish'])->name('install.finish');
});
