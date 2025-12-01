<?php

namespace Botble\MultiCountrySync\Listeners;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Ecommerce\Models\Product;
use Botble\MultiCountrySync\Jobs\SyncProductJob;

class SyncProductListener
{
    public function handle(CreatedContentEvent|UpdatedContentEvent $event): void
    {
        // Load ecommerce constants if not already loaded
        if (! defined('PRODUCT_MODULE_SCREEN_NAME')) {
            if (file_exists(plugin_path('ecommerce/helpers/constants.php'))) {
                require_once plugin_path('ecommerce/helpers/constants.php');
            }
        }
        
        // Only sync products
        if (! defined('PRODUCT_MODULE_SCREEN_NAME') || $event->screen !== PRODUCT_MODULE_SCREEN_NAME) {
            return;
        }

        $product = $event->data;

        if (! $product instanceof Product) {
            return;
        }

        // Skip variations (they sync with parent)
        if ($product->is_variation) {
            return;
        }

        // Check if sync is enabled
        if (! config('plugins.multi-country-sync.sync.enabled')) {
            return;
        }

        // Determine action
        $action = $event instanceof CreatedContentEvent ? 'create' : 'update';

        // Check if this action should be synced
        if ($action === 'create' && ! config('plugins.multi-country-sync.sync.sync_on_create', true)) {
            return;
        }

        if ($action === 'update' && ! config('plugins.multi-country-sync.sync.sync_on_update', true)) {
            return;
        }

        // Check if queue should be used
        $useQueue = config('plugins.multi-country-sync.sync.use_queue', true);

        if ($useQueue) {
            // Dispatch job to queue
            SyncProductJob::dispatch($product->id, $action)
                ->onQueue(config('plugins.multi-country-sync.sync.queue_name', 'product-sync'));
        } else {
            // Run synchronously
            $job = new SyncProductJob($product->id, $action);
            $job->handle(app(\Botble\MultiCountrySync\Services\ProductSyncService::class));
        }
    }
}

