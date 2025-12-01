<?php

namespace Botble\MultiCountrySync\Listeners;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Ecommerce\Models\Product;
use Botble\MultiCountrySync\Services\ProductSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncProductListener implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    public string $queue = 'product-sync';

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

        // Get product ID from event and reload fresh instance to avoid serialization issues
        $productId = $event->data->id ?? null;
        
        if (! $productId) {
            return;
        }

        // Reload product fresh from database to avoid serialization issues with relationships
        $product = Product::query()->find($productId);

        if (! $product) {
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

        // Resolve service from container to avoid serialization issues
        $syncService = app(ProductSyncService::class);

        // Sync product to other instances
        if ($event instanceof CreatedContentEvent) {
            if (config('plugins.multi-country-sync.sync.sync_on_create', true)) {
                $syncService->syncProduct($product, 'create');
            }
        } else {
            if (config('plugins.multi-country-sync.sync.sync_on_update', true)) {
                $syncService->syncProduct($product, 'update');
            }
        }
    }

    protected function shouldSync(Product $product): bool
    {
        // Only sync published products
        return $product->status === BaseStatusEnum::PUBLISHED;
    }
}

