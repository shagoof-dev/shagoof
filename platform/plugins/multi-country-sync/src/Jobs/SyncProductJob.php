<?php

namespace Botble\MultiCountrySync\Jobs;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Ecommerce\Models\Product;
use Botble\MultiCountrySync\Services\ProductSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $queue = 'product-sync';

    public function __construct(
        public int $productId,
        public string $action // 'create' or 'update'
    ) {
    }

    public function handle(ProductSyncService $syncService): void
    {
        // Load ecommerce constants if not already loaded
        if (! defined('PRODUCT_MODULE_SCREEN_NAME')) {
            if (file_exists(plugin_path('ecommerce/helpers/constants.php'))) {
                require_once plugin_path('ecommerce/helpers/constants.php');
            }
        }

        // Check if sync is enabled
        if (! config('plugins.multi-country-sync.sync.enabled')) {
            return;
        }

        // Reload product fresh from database
        $product = Product::query()->find($this->productId);

        if (! $product) {
            return;
        }

        // Skip variations (they sync with parent)
        if ($product->is_variation) {
            return;
        }

        // Check if product should be synced
        if ($product->status !== BaseStatusEnum::PUBLISHED) {
            return;
        }

        // Sync product to other instances
        $syncService->syncProduct($product, $this->action);
    }
}

