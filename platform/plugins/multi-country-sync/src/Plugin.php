<?php

namespace Botble\MultiCountrySync;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public static function activated(): void
    {
        // Migrations are handled by loadMigrations() in ServiceProvider
        // No need to run migrations here as they're auto-loaded
    }

    public static function remove(): void
    {
        Schema::dropIfExists('multi_country_sync_logs');
    }
}

