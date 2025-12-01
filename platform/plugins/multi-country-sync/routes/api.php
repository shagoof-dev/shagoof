<?php

use Botble\MultiCountrySync\Http\Controllers\Api\TestConnectionController;
use Botble\MultiCountrySync\Http\Controllers\SyncController;
use Botble\MultiCountrySync\Http\Middleware\ApiKeyAuthMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['api', ApiKeyAuthMiddleware::class])->prefix('sync')->group(function () {
    Route::post('products', [SyncController::class, 'syncProduct']);
    Route::put('products/{id}', [SyncController::class, 'syncProduct']);
    Route::delete('products/{id}', [SyncController::class, 'deleteProduct']);
    Route::get('test', function () {
        return response()->json(['status' => 'ok', 'message' => 'Sync API is working']);
    });
});

