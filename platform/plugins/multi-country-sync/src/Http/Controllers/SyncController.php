<?php

namespace Botble\MultiCountrySync\Http\Controllers;

use Botble\Base\Facades\MetaBox as MetaBoxFacade;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Models\MetaBox;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Services\Products\StoreProductService;
use Botble\MultiCountrySync\Http\Requests\SyncProductRequest;
use Illuminate\Http\Request;

class SyncController extends BaseController
{
    public function __construct(
        protected StoreProductService $storeProductService
    ) {
    }

    public function syncProduct(SyncProductRequest $request, BaseHttpResponse $response)
    {
        $data = $request->validated();
        
        // Check if product already exists (by source_product_id or SKU)
        $product = $this->findExistingProduct($data);
        
        if ($product) {
            // Update existing product
            $product = $this->storeProductService->execute(
                new Request($data),
                $product,
                true
            );
            
            return $response
                ->setData(['product_id' => $product->id, 'action' => 'updated'])
                ->setMessage('Product updated successfully');
        }
        
        // Create new product
        $product = new Product();
        $product->status = $data['status'] ?? 'published';
        $product = $this->storeProductService->execute(
            new Request($data),
            $product,
            true
        );
        
        // Store sync metadata
        if (isset($data['sync_metadata'])) {
            MetaBoxFacade::saveMetaBoxData(
                $product,
                'sync_metadata',
                $data['sync_metadata']
            );
        }
        
        return $response
            ->setData(['product_id' => $product->id, 'action' => 'created'])
            ->setMessage('Product synced successfully');
    }

    protected function findExistingProduct(array $data): ?Product
    {
        // Try to find by source_product_id from metadata
        if (isset($data['sync_metadata']['source_product_id'])) {
            $sourceProductId = $data['sync_metadata']['source_product_id'];
            
            // Search in metadata using MetaBox model
            // MetaBox stores meta_value as JSON array, so we check the first element
            $metaBoxes = MetaBox::query()
                ->where('meta_key', 'sync_metadata')
                ->where('reference_type', Product::class)
                ->get();
            
            foreach ($metaBoxes as $metaBox) {
                $metaValue = $metaBox->meta_value;
                // meta_value is stored as array, check first element
                if (is_array($metaValue) && isset($metaValue[0]) && is_array($metaValue[0])) {
                    if (isset($metaValue[0]['source_product_id']) && $metaValue[0]['source_product_id'] == $sourceProductId) {
                        $product = Product::query()->find($metaBox->reference_id);
                        if ($product) {
                            return $product;
                        }
                    }
                }
            }
        }
        
        // Try to find by SKU
        if (isset($data['sku']) && !empty($data['sku'])) {
            return Product::query()
                ->where('sku', $data['sku'])
                ->where('is_variation', 0)
                ->first();
        }
        
        return null;
    }

    public function deleteProduct(int $id, BaseHttpResponse $response)
    {
        $product = Product::query()
            ->where('id', $id)
            ->where('is_variation', 0)
            ->first();
        
        if (! $product) {
            return $response
                ->setError()
                ->setMessage('Product not found')
                ->setStatusCode(404);
        }
        
        $product->delete();
        
        return $response
            ->setMessage('Product deleted successfully');
    }
}

