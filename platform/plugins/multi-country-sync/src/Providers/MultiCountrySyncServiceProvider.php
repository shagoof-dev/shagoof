<?php

namespace Botble\MultiCountrySync\Providers;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\MultiCountrySync\Listeners\SyncProductListener;
use Botble\MultiCountrySync\PanelSections\SyncPanelSection;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

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
            ->loadRoutes(['api', 'web'])
            ->loadAndPublishViews()
            ->loadAndPublishTranslations()
            ->publishAssets();

        PanelSectionManager::default()->register(SyncPanelSection::class);
    }
}

