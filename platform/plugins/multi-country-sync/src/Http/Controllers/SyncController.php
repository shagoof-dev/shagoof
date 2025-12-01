<?php

namespace Botble\MultiCountrySync\Http\Controllers;

use Botble\Base\Facades\MetaBox as MetaBoxFacade;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Models\MetaBox;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Services\Products\StoreProductService;
use Botble\Media\Facades\RvMedia;
use Botble\MultiCountrySync\Http\Requests\SyncProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SyncController extends BaseController
{
    public function __construct(
        protected StoreProductService $storeProductService
    ) {
    }

    public function syncProduct(SyncProductRequest $request, BaseHttpResponse $response)
    {
        $data = $request->validated();
        
        // Download and upload images from source server
        $data = $this->processImages($data);
        
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

    protected function processImages(array $data): array
    {
        // Process product images array
        if (isset($data['images']) && is_array($data['images'])) {
            $uploadedImages = [];
            
            foreach ($data['images'] as $imageUrl) {
                if (empty($imageUrl)) {
                    continue;
                }
                
                // If it's already a local filename (not a URL), use it as-is
                if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                    $uploadedImages[] = $imageUrl;
                    continue;
                }
                
                // Download and upload image from URL
                try {
                    Log::info('Multi-Country Sync: Downloading image from URL', ['url' => $imageUrl]);
                    
                    $result = RvMedia::uploadFromUrl($imageUrl, 0, 'products');
                    
                    if (!($result['error'] ?? true) && isset($result['data'])) {
                        $uploadedImages[] = $result['data']->url;
                        Log::info('Multi-Country Sync: Image uploaded successfully', [
                            'original_url' => $imageUrl,
                            'new_path' => $result['data']->url,
                        ]);
                    } else {
                        Log::warning('Multi-Country Sync: Failed to upload image', [
                            'url' => $imageUrl,
                            'error' => $result['message'] ?? 'Unknown error',
                        ]);
                        // Keep original URL if upload fails (might be external URL)
                        $uploadedImages[] = $imageUrl;
                    }
                } catch (\Exception $e) {
                    Log::error('Multi-Country Sync: Exception uploading image', [
                        'url' => $imageUrl,
                        'error' => $e->getMessage(),
                    ]);
                    // Keep original URL if upload fails
                    $uploadedImages[] = $imageUrl;
                }
            }
            
            $data['images'] = $uploadedImages;
        }
        
        // Process featured image (image field)
        if (isset($data['image']) && !empty($data['image'])) {
            $imageUrl = $data['image'];
            
            // If it's already a local filename (not a URL), use it as-is
            if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                // Already a local path, keep it
                return $data;
            }
            
            // Download and upload image from URL
            try {
                Log::info('Multi-Country Sync: Downloading featured image from URL', ['url' => $imageUrl]);
                
                $result = RvMedia::uploadFromUrl($imageUrl, 0, 'products');
                
                if (!($result['error'] ?? true) && isset($result['data'])) {
                    $data['image'] = $result['data']->url;
                    Log::info('Multi-Country Sync: Featured image uploaded successfully', [
                        'original_url' => $imageUrl,
                        'new_path' => $result['data']->url,
                    ]);
                } else {
                    Log::warning('Multi-Country Sync: Failed to upload featured image', [
                        'url' => $imageUrl,
                        'error' => $result['message'] ?? 'Unknown error',
                    ]);
                    // Keep original URL if upload fails
                }
            } catch (\Exception $e) {
                Log::error('Multi-Country Sync: Exception uploading featured image', [
                    'url' => $imageUrl,
                    'error' => $e->getMessage(),
                ]);
                // Keep original URL if upload fails
            }
        }
        
        return $data;
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

