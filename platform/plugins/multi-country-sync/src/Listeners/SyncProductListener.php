<?php

namespace Botble\MultiCountrySync\Listeners;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Ecommerce\Models\Product;
use Botble\MultiCountrySync\Jobs\SyncProductJob;
use Illuminate\Support\Facades\Log;

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
            Log::debug('Multi-Country Sync: Skipping - not a product event', [
                'screen' => $event->screen ?? 'undefined',
                'defined' => defined('PRODUCT_MODULE_SCREEN_NAME'),
            ]);
            return;
        }

        $product = $event->data;

        if (! $product instanceof Product) {
            Log::debug('Multi-Country Sync: Skipping - not a Product instance', [
                'type' => get_class($product),
            ]);
            return;
        }

        // Skip variations (they sync with parent)
        if ($product->is_variation) {
            Log::debug('Multi-Country Sync: Skipping - product is a variation', [
                'product_id' => $product->id,
            ]);
            return;
        }

        // Check if sync is enabled
        $enabled = config('plugins.multi-country-sync.sync.enabled');
        if (! $enabled) {
            Log::debug('Multi-Country Sync: Skipping - sync is disabled', [
                'product_id' => $product->id,
                'enabled' => $enabled,
            ]);
            return;
        }

        // Determine action
        $action = $event instanceof CreatedContentEvent ? 'create' : 'update';

        Log::info('Multi-Country Sync: Processing product', [
            'product_id' => $product->id,
            'action' => $action,
            'product_name' => $product->name,
        ]);

        // Check if this action should be synced
        if ($action === 'create' && ! config('plugins.multi-country-sync.sync.sync_on_create', true)) {
            Log::debug('Multi-Country Sync: Skipping - sync_on_create is disabled');
            return;
        }

        if ($action === 'update' && ! config('plugins.multi-country-sync.sync.sync_on_update', true)) {
            Log::debug('Multi-Country Sync: Skipping - sync_on_update is disabled');
            return;
        }

        // Check if queue should be used
        $useQueue = config('plugins.multi-country-sync.sync.use_queue', true);

        if ($useQueue) {
            // Dispatch job to queue
            Log::info('Multi-Country Sync: Dispatching job to queue', [
                'product_id' => $product->id,
                'action' => $action,
                'queue' => config('plugins.multi-country-sync.sync.queue_name', 'product-sync'),
            ]);
            SyncProductJob::dispatch($product->id, $action)
                ->onQueue(config('plugins.multi-country-sync.sync.queue_name', 'product-sync'));
        } else {
            // Run synchronously
            Log::info('Multi-Country Sync: Running synchronously', [
                'product_id' => $product->id,
                'action' => $action,
            ]);
            $job = new SyncProductJob($product->id, $action);
            $job->handle(app(\Botble\MultiCountrySync\Services\ProductSyncService::class));
        }
    }
}

