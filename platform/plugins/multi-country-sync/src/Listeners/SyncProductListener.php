<?php

namespace Botble\MultiCountrySync\Listeners;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Ecommerce\Models\Product;
use Botble\MultiCountrySync\Services\ProductSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SyncProductListener implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'product-sync';

    public function __construct(
        protected ProductSyncService $syncService
    ) {
    }

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

        // Check if product should be synced
        if (! $this->shouldSync($product)) {
            return;
        }

        // Sync product to other instances
        if ($event instanceof CreatedContentEvent) {
            if (config('plugins.multi-country-sync.sync.sync_on_create', true)) {
                $this->syncService->syncProduct($product, 'create');
            }
        } else {
            if (config('plugins.multi-country-sync.sync.sync_on_update', true)) {
                $this->syncService->syncProduct($product, 'update');
            }
        }
    }

    protected function shouldSync(Product $product): bool
    {
        // Only sync published products
        return $product->status === BaseStatusEnum::PUBLISHED;
    }
}

