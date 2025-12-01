<?php

use Botble\Base\Facades\AdminHelper;
use Botble\MultiCountrySync\Http\Controllers\Api\TestConnectionController;
use Botble\MultiCountrySync\Http\Controllers\Settings\SyncSettingController;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function (): void {
    Route::group([
        'namespace' => 'Botble\MultiCountrySync\Http\Controllers\Settings',
    ], function (): void {
        Route::prefix('multi-country-sync')->name('multi-country-sync.')->group(function (): void {
            Route::prefix('settings')->group(function (): void {
                Route::get('/', [
                    'as' => 'settings',
                    'uses' => SyncSettingController::class . '@edit',
                ]);

                Route::put('/', [
                    'as' => 'settings.update',
                    'uses' => SyncSettingController::class . '@update',
                    'permission' => 'multi-country-sync.settings',
                ]);

                Route::post('test-connection', [
                    'as' => 'settings.test-connection',
                    'uses' => \Botble\MultiCountrySync\Http\Controllers\Api\TestConnectionController::class . '@test',
                    'permission' => 'multi-country-sync.settings',
                ]);

                Route::post('generate-api-key', [
                    'as' => 'settings.generate-api-key',
                    'uses' => SyncSettingController::class . '@generateApiKey',
                    'permission' => 'multi-country-sync.settings',
                ]);
            });
        });
    });
});

