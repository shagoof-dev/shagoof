<?php

namespace Botble\MultiCountrySync\Providers;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\MultiCountrySync\Http\Controllers\SyncController;
use Botble\MultiCountrySync\Http\Middleware\ApiKeyAuthMiddleware;
use Botble\MultiCountrySync\Listeners\SyncProductListener;
use Botble\MultiCountrySync\PanelSections\SyncPanelSection;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class MultiCountrySyncServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    protected $listen = [
        CreatedContentEvent::class => [
            SyncProductListener::class,
        ],
        UpdatedContentEvent::class => [
            SyncProductListener::class,
        ],
    ];

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/multi-country-sync')
            ->loadAndPublishConfigurations(['sync'])
            ->loadMigrations()
            ->loadHelpers()
            ->loadRoutes(['web'])
            ->loadAndPublishViews()
            ->loadAndPublishTranslations()
            ->publishAssets();

        // Register API routes explicitly
        $this->registerApiRoutes();

        PanelSectionManager::default()->register(SyncPanelSection::class);
    }

    protected function registerApiRoutes(): void
    {
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
    }
}

