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
use Botble\Slug\Facades\SlugHelper;
use Botble\Slug\Models\Slug;
use Botble\Slug\Services\SlugService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
        
        // Ensure slug uniqueness and generate if needed
        $data = $this->ensureSlugUniqueness($data);
        
        // Check if product already exists (by source_product_id or SKU)
        $product = $this->findExistingProduct($data);
        
        // Set is_slug_editable to ensure slug is created in slugs table
        $data['is_slug_editable'] = 1;
        
        if ($product) {
            // Update existing product
            $product = $this->storeProductService->execute(
                new Request($data),
                $product,
                true
            );
            
            // Ensure slug entry exists in slugs table
            $this->ensureSlugEntry($product, $data['slug'] ?? null);
            
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
        
        // Ensure slug entry exists in slugs table
        $this->ensureSlugEntry($product, $data['slug'] ?? null);
        
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
                    Log::debug('Multi-Country Sync: Image already local path', ['path' => $imageUrl]);
                    continue;
                }
                
                // Download and upload image from URL
                try {
                    Log::info('Multi-Country Sync: Downloading image from URL', ['url' => $imageUrl]);
                    
                    $result = RvMedia::uploadFromUrl($imageUrl, 0, 'products');
                    
                    // Check result structure
                    Log::debug('Multi-Country Sync: uploadFromUrl result', [
                        'has_error' => isset($result['error']),
                        'error' => $result['error'] ?? null,
                        'has_data' => isset($result['data']),
                        'data_type' => isset($result['data']) ? get_class($result['data']) : null,
                    ]);
                    
                    if (isset($result['error']) && $result['error'] === false && isset($result['data'])) {
                        // Get the relative path from MediaFile model
                        $mediaFile = $result['data'];
                        $imagePath = $mediaFile->url; // This should be the relative path
                        
                        $uploadedImages[] = $imagePath;
                        Log::info('Multi-Country Sync: Image uploaded successfully', [
                            'original_url' => $imageUrl,
                            'new_path' => $imagePath,
                            'media_file_id' => $mediaFile->id ?? null,
                        ]);
                    } else {
                        $errorMessage = $result['message'] ?? 'Unknown error';
                        Log::warning('Multi-Country Sync: Failed to upload image', [
                            'url' => $imageUrl,
                            'error' => $errorMessage,
                            'result' => $result,
                        ]);
                        // Don't add failed images - skip them
                        continue;
                    }
                } catch (\Exception $e) {
                    Log::error('Multi-Country Sync: Exception uploading image', [
                        'url' => $imageUrl,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // Don't add failed images - skip them
                    continue;
                }
            }
            
            $data['images'] = $uploadedImages;
            Log::info('Multi-Country Sync: Processed images', [
                'original_count' => count($data['images'] ?? []),
                'uploaded_count' => count($uploadedImages),
                'final_images' => $uploadedImages,
            ]);
        }
        
        // Process featured image (image field)
        if (isset($data['image']) && !empty($data['image'])) {
            $imageUrl = $data['image'];
            
            // If it's already a local filename (not a URL), use it as-is
            if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                // Already a local path, keep it
                Log::debug('Multi-Country Sync: Featured image already local path', ['path' => $imageUrl]);
                return $data;
            }
            
            // Download and upload image from URL
            try {
                Log::info('Multi-Country Sync: Downloading featured image from URL', ['url' => $imageUrl]);
                
                $result = RvMedia::uploadFromUrl($imageUrl, 0, 'products');
                
                Log::debug('Multi-Country Sync: uploadFromUrl result for featured image', [
                    'has_error' => isset($result['error']),
                    'error' => $result['error'] ?? null,
                    'has_data' => isset($result['data']),
                ]);
                
                if (isset($result['error']) && $result['error'] === false && isset($result['data'])) {
                    $mediaFile = $result['data'];
                    $data['image'] = $mediaFile->url;
                    Log::info('Multi-Country Sync: Featured image uploaded successfully', [
                        'original_url' => $imageUrl,
                        'new_path' => $mediaFile->url,
                        'media_file_id' => $mediaFile->id ?? null,
                    ]);
                } else {
                    $errorMessage = $result['message'] ?? 'Unknown error';
                    Log::warning('Multi-Country Sync: Failed to upload featured image', [
                        'url' => $imageUrl,
                        'error' => $errorMessage,
                        'result' => $result,
                    ]);
                    // Remove image if upload fails
                    unset($data['image']);
                }
            } catch (\Exception $e) {
                Log::error('Multi-Country Sync: Exception uploading featured image', [
                    'url' => $imageUrl,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                // Remove image if upload fails
                unset($data['image']);
            }
        }
        
        return $data;
    }

    protected function ensureSlugEntry(Product $product, ?string $slug = null): void
    {
        // If slug is not provided, use the product's slug or generate from name
        if (empty($slug)) {
            $slug = $product->slug;
            if (empty($slug) && !empty($product->name)) {
                $slug = Str::slug($product->name);
            }
        }
        
        if (empty($slug)) {
            Log::warning('Multi-Country Sync: Cannot create slug entry - no slug or name available', [
                'product_id' => $product->id,
            ]);
            return;
        }
        
        // Check if slug entry already exists
        $existingSlug = Slug::query()
            ->where('reference_type', Product::class)
            ->where('reference_id', $product->id)
            ->first();
        
        if ($existingSlug) {
            // Update existing slug if different
            if ($existingSlug->key !== $slug) {
                $slugService = new SlugService();
                $uniqueSlug = $slugService->create($slug, $existingSlug->id, Product::class);
                
                $existingSlug->key = $uniqueSlug;
                $existingSlug->saveQuietly();
                
                // Update product slug column
                $product->slug = $uniqueSlug;
                $product->saveQuietly();
                
                Log::info('Multi-Country Sync: Updated slug entry', [
                    'product_id' => $product->id,
                    'old_slug' => $existingSlug->key,
                    'new_slug' => $uniqueSlug,
                ]);
            }
            return;
        }
        
        // Create new slug entry
        try {
            $slugService = new SlugService();
            $uniqueSlug = $slugService->create($slug, 0, Product::class);
            
            Slug::query()->create([
                'key' => $uniqueSlug,
                'reference_type' => Product::class,
                'reference_id' => $product->id,
                'prefix' => SlugHelper::getPrefix(Product::class, '', false),
            ]);
            
            // Update product slug column
            $product->slug = $uniqueSlug;
            $product->saveQuietly();
            
            Log::info('Multi-Country Sync: Created slug entry', [
                'product_id' => $product->id,
                'slug' => $uniqueSlug,
            ]);
        } catch (\Exception $e) {
            Log::error('Multi-Country Sync: Failed to create slug entry', [
                'product_id' => $product->id,
                'slug' => $slug,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function ensureSlugUniqueness(array $data): array
    {
        $slugService = new SlugService();
        $productId = null;
        
        // If updating existing product, get its ID for slug uniqueness check
        $existingProduct = $this->findExistingProduct($data);
        if ($existingProduct) {
            $productId = $existingProduct->id;
            
            // Get existing slug ID if slug entry exists
            $existingSlug = Slug::query()
                ->where('reference_type', Product::class)
                ->where('reference_id', $productId)
                ->first();
            
            if ($existingSlug) {
                $productId = $existingSlug->id; // Use slug ID for uniqueness check
            }
        }
        
        // If slug is provided, ensure it's unique using SlugService
        if (isset($data['slug']) && !empty($data['slug'])) {
            $slug = $data['slug'];
            $uniqueSlug = $slugService->create($slug, $productId ?? 0, Product::class);
            
            if ($uniqueSlug !== $slug) {
                Log::info('Multi-Country Sync: Slug conflict resolved', [
                    'original_slug' => $slug,
                    'new_slug' => $uniqueSlug,
                    'product_id' => $productId,
                ]);
            }
            
            $data['slug'] = $uniqueSlug;
        } elseif (isset($data['name']) && !empty($data['name'])) {
            // If no slug provided but name exists, generate slug from name
            $slug = Str::slug($data['name']);
            $uniqueSlug = $slugService->create($slug, $productId ?? 0, Product::class);
            
            $data['slug'] = $uniqueSlug;
            
            Log::info('Multi-Country Sync: Generated slug from name', [
                'name' => $data['name'],
                'slug' => $uniqueSlug,
            ]);
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

