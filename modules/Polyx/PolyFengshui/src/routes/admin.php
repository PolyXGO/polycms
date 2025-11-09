<?php

use Illuminate\Support\Facades\Route;
use Modules\Polyx\PolyFengshui\Http\Controllers\Admin\TokenController;

Route::get('/tokens', [TokenController::class, 'index'])->name('tokens');
Route::post('/tokens', [TokenController::class, 'store'])->name('tokens.store');
Route::delete('/tokens/{tokenId}', [TokenController::class, 'destroy'])->name('tokens.destroy');
Route::post('/tokens/settings', [TokenController::class, 'updateSettings'])->name('tokens.settings');

