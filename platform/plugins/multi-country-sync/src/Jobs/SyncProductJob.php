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
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $productId,
        public string $action // 'create' or 'update'
    ) {
        $this->onQueue(config('plugins.multi-country-sync.sync.queue_name', 'product-sync'));
    }

    public function handle(ProductSyncService $syncService): void
    {
        Log::info('Multi-Country Sync Job: Starting', [
            'product_id' => $this->productId,
            'action' => $this->action,
        ]);

        try {
            // Load ecommerce constants if not already loaded
            if (! defined('PRODUCT_MODULE_SCREEN_NAME')) {
                if (file_exists(plugin_path('ecommerce/helpers/constants.php'))) {
                    require_once plugin_path('ecommerce/helpers/constants.php');
                }
            }

            // Check if sync is enabled
            if (! config('plugins.multi-country-sync.sync.enabled')) {
                Log::warning('Multi-Country Sync Job: Sync is disabled', [
                    'product_id' => $this->productId,
                ]);
                return;
            }

            // Reload product fresh from database
            $product = Product::query()->find($this->productId);

            if (! $product) {
                Log::warning('Multi-Country Sync Job: Product not found', [
                    'product_id' => $this->productId,
                ]);
                return;
            }

            // Skip variations (they sync with parent)
            if ($product->is_variation) {
                Log::debug('Multi-Country Sync Job: Skipping variation', [
                    'product_id' => $this->productId,
                ]);
                return;
            }

            // Check if product should be synced
            // Note: status is cast to BaseStatusEnum, so we compare enum values
            if ($product->status != BaseStatusEnum::PUBLISHED) {
                Log::debug('Multi-Country Sync Job: Product not published', [
                    'product_id' => $this->productId,
                    'status' => $product->status?->getValue() ?? 'null',
                    'status_object' => $product->status,
                ]);
                return;
            }

            Log::info('Multi-Country Sync Job: Calling syncService', [
                'product_id' => $this->productId,
                'action' => $this->action,
                'product_name' => $product->name,
            ]);

            // Sync product to other instances
            $syncService->syncProduct($product, $this->action);

            Log::info('Multi-Country Sync Job: Completed successfully', [
                'product_id' => $this->productId,
                'action' => $this->action,
            ]);
        } catch (Throwable $e) {
            Log::error('Multi-Country Sync Job: Failed', [
                'product_id' => $this->productId,
                'action' => $this->action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e; // Re-throw to mark job as failed
        }
    }
}

