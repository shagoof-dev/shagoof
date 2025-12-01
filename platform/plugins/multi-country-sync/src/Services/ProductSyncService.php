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
        $instances = config('plugins.multi-country-sync.sync.instances', []);
        $currentCountry = config('plugins.multi-country-sync.sync.current_country', 'eg');

        foreach ($instances as $instanceKey => $instance) {
            // Skip current country
            if ($instanceKey === $currentCountry) {
                continue;
            }

            if (! ($instance['enabled'] ?? true)) {
                continue;
            }

            try {
                $this->syncToInstance($product, $instance, $instanceKey, $action);
            } catch (Exception $e) {
                Log::error("Failed to sync product {$product->id} to {$instanceKey}: " . $e->getMessage());
                
                // Log sync failure
                SyncLog::create([
                    'product_id' => $product->id,
                    'instance' => $instanceKey,
                    'action' => $action,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }
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
        
        // Handle images
        if (isset($data['images'])) {
            $data['images'] = json_decode($data['images'], true) ?: [];
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

