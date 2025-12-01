<?php

use Botble\MultiCountrySync\Http\Controllers\Api\TestConnectionController;
use Botble\MultiCountrySync\Http\Controllers\SyncController;
use Botble\MultiCountrySync\Http\Middleware\ApiKeyAuthMiddleware;
use Illuminate\Support\Facades\Route;

// Register API routes - these will be accessible at /api/sync/*
// Note: Laravel 11 doesn't auto-prefix API routes, so we need to include 'api' in the prefix
Route::group([
    'middleware' => ['api', ApiKeyAuthMiddleware::class],
    'prefix' => 'api/sync',
], function () {
    Route::post('products', [SyncController::class, 'syncProduct']);
    Route::put('products/{id}', [SyncController::class, 'syncProduct']);
    Route::delete('products/{id}', [SyncController::class, 'deleteProduct']);
    Route::get('test', function () {
        return response()->json(['status' => 'ok', 'message' => 'Sync API is working']);
    });
});

