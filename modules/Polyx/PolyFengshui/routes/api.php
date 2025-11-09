<?php

use Illuminate\Support\Facades\Route;
use Modules\Polyx\PolyFengshui\Http\Controllers\Api\V1\DateController;
use Modules\Polyx\PolyFengshui\Http\Controllers\Api\V1\MovingDateController;
use Modules\Polyx\PolyFengshui\Http\Controllers\Api\V1\MovingDateLookupController;

Route::prefix('v1/polyfengshui')
    ->name('api.v1.polyfengshui.')
    ->group(function () {
        Route::get('/date', [DateController::class, 'index'])->name('date.index');
        Route::get('/moving-date', [MovingDateController::class, 'index'])->name('moving-date.index');
        Route::get('/moving-date-lookup', [MovingDateLookupController::class, 'index'])->name('moving-date-lookup.index');
    });

