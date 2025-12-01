<?php

namespace Botble\MultiCountrySync\Services;

use Botble\Ecommerce\Models\Product;
use Botble\MultiCountrySync\Models\SyncLog;
use Exception;
use Illuminate\Support\Facades\Log;

class ProductSyncService
{
    public function __construct(
        protected ApiClientService $apiClient
    ) {
    }

    public function syncProduct(Product $product, string $action = 'create'): void
    {
        Log::info('ProductSyncService: Starting sync', [
            'product_id' => $product->id,
            'action' => $action,
        ]);

        $instances = config('plugins.multi-country-sync.sync.instances', []);
        $currentCountry = config('plugins.multi-country-sync.sync.current_country', 'eg');

        Log::info('ProductSyncService: Config loaded', [
            'current_country' => $currentCountry,
            'instances_count' => is_array($instances) ? count($instances) : 0,
            'instances' => array_keys($instances ?? []),
        ]);

        if (! is_array($instances) || empty($instances)) {
            Log::warning('ProductSyncService: No instances configured', [
                'product_id' => $product->id,
            ]);
            return;
        }

        $syncedCount = 0;
        foreach ($instances as $instanceKey => $instance) {
            // Skip current country
            if ($instanceKey === $currentCountry) {
                Log::debug('ProductSyncService: Skipping current country', [
                    'instance' => $instanceKey,
                    'current_country' => $currentCountry,
                ]);
                continue;
            }

            if (! ($instance['enabled'] ?? true)) {
                Log::debug('ProductSyncService: Instance disabled', [
                    'instance' => $instanceKey,
                ]);
                continue;
            }

            if (empty($instance['url']) || empty($instance['api_key'])) {
                Log::warning('ProductSyncService: Instance missing URL or API key', [
                    'instance' => $instanceKey,
                    'has_url' => !empty($instance['url']),
                    'has_api_key' => !empty($instance['api_key']),
                ]);
                continue;
            }

            try {
                Log::info('ProductSyncService: Syncing to instance', [
                    'product_id' => $product->id,
                    'instance' => $instanceKey,
                    'action' => $action,
                ]);
                
                $this->syncToInstance($product, $instance, $instanceKey, $action);
                $syncedCount++;
                
                Log::info('ProductSyncService: Successfully synced to instance', [
                    'product_id' => $product->id,
                    'instance' => $instanceKey,
                ]);
            } catch (Exception $e) {
                Log::error("ProductSyncService: Failed to sync product {$product->id} to {$instanceKey}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                
                // Log sync failure
                try {
                    SyncLog::create([
                        'product_id' => $product->id,
                        'instance' => $instanceKey,
                        'action' => $action,
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                    ]);
                } catch (Exception $logException) {
                    Log::error('ProductSyncService: Failed to create sync log', [
                        'error' => $logException->getMessage(),
                    ]);
                }
            }
        }

        Log::info('ProductSyncService: Sync completed', [
            'product_id' => $product->id,
            'action' => $action,
            'synced_count' => $syncedCount,
            'total_instances' => count($instances),
        ]);
    }

    protected function syncToInstance(Product $product, array $instance, string $instanceKey, string $action): void
    {
        $productData = $this->prepareProductData($product);

        $response = match ($action) {
            'create' => $this->apiClient->createProduct($instance['url'], $instance['api_key'], $productData),
            'update' => $this->apiClient->updateProduct($instance['url'], $instance['api_key'], $product->id, $productData),
            'delete' => $this->apiClient->deleteProduct($instance['url'], $instance['api_key'], $product->id),
            default => throw new Exception("Unknown action: {$action}"),
        };

        // Log successful sync
        SyncLog::create([
            'product_id' => $product->id,
            'instance' => $instanceKey,
            'action' => $action,
            'status' => 'success',
            'response_data' => $response,
        ]);
    }

    protected function prepareProductData(Product $product): array
    {
        $syncFields = config('plugins.multi-country-sync.sync.sync_fields', []);
        $excludeFields = config('plugins.multi-country-sync.sync.exclude_fields', []);
        
        $data = $product->only($syncFields);
        
        // Remove excluded fields
        $data = array_diff_key($data, array_flip($excludeFields));
        
        // Add relationships
        $syncRelationships = config('plugins.multi-country-sync.sync.sync_relationships', []);
        
        if ($syncRelationships['categories'] ?? false) {
            $data['categories'] = $product->categories->pluck('id')->toArray();
        }
        
        if ($syncRelationships['tags'] ?? false) {
            $data['tags'] = $product->tags->pluck('id')->toArray();
        }
        
        if ($syncRelationships['collections'] ?? false) {
            $data['product_collections'] = $product->productCollections->pluck('id')->toArray();
        }
        
        if ($syncRelationships['labels'] ?? false) {
            $data['product_labels'] = $product->productLabels->pluck('id')->toArray();
        }
        
        if ($syncRelationships['taxes'] ?? false) {
            $data['taxes'] = $product->taxes->pluck('id')->toArray();
        }
        
        // Handle images - ensure it's an array
        if (isset($data['images'])) {
            if (is_string($data['images'])) {
                $data['images'] = json_decode($data['images'], true) ?: [];
            } elseif (! is_array($data['images'])) {
                $data['images'] = [];
            }
        }
        
        // Handle dates
        if (isset($data['start_date']) && $data['start_date']) {
            $data['start_date'] = $data['start_date'] instanceof \Carbon\Carbon 
                ? $data['start_date']->toIso8601String() 
                : $data['start_date'];
        }
        
        if (isset($data['end_date']) && $data['end_date']) {
            $data['end_date'] = $data['end_date'] instanceof \Carbon\Carbon 
                ? $data['end_date']->toIso8601String() 
                : $data['end_date'];
        }
        
        // Add metadata for tracking
        $data['sync_metadata'] = [
            'source_instance' => config('plugins.multi-country-sync.sync.current_country', 'eg'),
            'source_url' => config('app.url'),
            'source_product_id' => $product->id,
            'synced_at' => now()->toIso8601String(),
        ];
        
        return $data;
    }
}

