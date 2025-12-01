# Multi-Country Product Synchronization Solution

## Project Overview

**Shagoof E-commerce Platform** - Multi-country deployment using Git branches

### Production URLs
- **EG (Egypt)**: https://eg.shagoof.com (Main Branch: `eg`)
- **UAE**: https://uae.shagoof.com (Branch: `uae`)
- **SA (Saudi Arabia)**: https://sa.shagoof.com (Branch: `sa`)

### Git Branch Structure
- **Main Branch**: `eg` - Egypt production
- **Branch**: `uae` - UAE production
- **Branch**: `sa` - Saudi Arabia production

**Note**: Code is maintained in separate Git branches. Switch between branches using:
```bash
git checkout eg    # Switch to Egypt branch
git checkout uae   # Switch to UAE branch
git checkout sa    # Switch to Saudi Arabia branch
```

## Problem Statement

You have **3 separate installations** of the same e-commerce platform deployed in **3 different countries** (EG, UAE, SA). Each country runs from its own Git branch but shares the same codebase structure. When a product is created in one country, you need it to be automatically synchronized to the other 2 countries.

**Current Situation:**
- 3 separate Git branches (`eg`, `uae`, `sa`)
- 3 separate production deployments
- 3 separate databases
- Products need to be created manually in each country
- No automatic synchronization

**Requirements:**
- When product is created in EG → sync to UAE and SA
- When product is updated in EG → sync update to UAE and SA
- Bidirectional sync: Changes in UAE/SA can also sync to EG (configurable)
- Handle conflicts (product already exists)
- Support authentication between instances
- Handle failures gracefully
- Support selective sync (some products may not need sync)

---

## Solution Architecture

### Option 1: Event-Driven API Synchronization (Recommended)

**How it works:**
1. When product is created/updated, fire an event
2. Event listener sends API request to other instances
3. Other instances receive request and create/update product
4. Handle responses and errors

**Pros:**
- Real-time synchronization
- Event-driven (non-blocking)
- Can use queues for reliability
- Flexible and extensible

**Cons:**
- Requires API endpoints on all instances
- Network dependency
- Need to handle failures/retries

### Option 2: Central API Hub

**How it works:**
1. One central API server
2. All instances sync with central server
3. Central server distributes to all instances

**Pros:**
- Single point of control
- Easier to manage
- Can add analytics/logging

**Cons:**
- Single point of failure
- Additional infrastructure needed
- More complex setup

### Option 3: Scheduled Sync Job

**How it works:**
1. Scheduled job runs periodically (e.g., every 5 minutes)
2. Checks for new/updated products
3. Syncs to other instances

**Pros:**
- Simple implementation
- Can batch sync
- Less network overhead

**Cons:**
- Not real-time
- Delay in synchronization
- Need to track what's synced

---

## Recommended Implementation: Event-Driven API Synchronization

### Architecture Overview

```
EG (eg.shagoof.com)         UAE (uae.shagoof.com)      SA (sa.shagoof.com)
     │                          │                          │
     │ Product Created          │                          │
     ├──────────────────────────┼──────────────────────────┤
     │                          │                          │
     │ 1. Fire Event            │                          │
     │ 2. Send API Request ────>│                          │
     │ 3. Send API Request ──────────────────────────────>│
     │                          │                          │
     │                          │ 4. Create Product       │
     │                          │ 5. Return Response       │
     │                          │                          │
     │ 6. Handle Response <─────│                          │
     │ 7. Handle Response <────────────────────────────────│
```

**Git Branch Workflow:**
- Each country has its own Git branch
- Code changes are made in respective branches
- Sync plugin code should be identical across all branches
- Configuration differs per branch (API URLs, keys)

### Implementation Steps

#### Step 1: Create Sync Plugin

Create a new plugin: `platform/plugins/multi-country-sync/`

**Structure:**
```
multi-country-sync/
├── plugin.json
├── config/
│   └── sync.php
├── src/
│   ├── Plugin.php
│   ├── Providers/
│   │   └── MultiCountrySyncServiceProvider.php
│   ├── Listeners/
│   │   └── SyncProductListener.php
│   ├── Services/
│   │   ├── ProductSyncService.php
│   │   └── ApiClientService.php
│   ├── Http/
│   │   └── Controllers/
│   │       └── SyncController.php
│   └── Models/
│       └── SyncLog.php
└── routes/
    └── api.php
```

#### Step 2: Configuration File

**`config/sync.php`:**
```php
<?php

return [
    'enabled' => env('MULTI_COUNTRY_SYNC_ENABLED', false),
    
    // Current country identifier
    'current_country' => env('CURRENT_COUNTRY', 'eg'), // eg, uae, or sa
    
    // Other country instances
    'instances' => [
        'uae' => [
            'url' => env('UAE_API_URL', 'https://uae.shagoof.com'),
            'api_key' => env('UAE_API_KEY'),
            'enabled' => env('UAE_SYNC_ENABLED', true),
        ],
        'sa' => [
            'url' => env('SA_API_URL', 'https://sa.shagoof.com'),
            'api_key' => env('SA_API_KEY'),
            'enabled' => env('SA_SYNC_ENABLED', true),
        ],
        'eg' => [
            'url' => env('EG_API_URL', 'https://eg.shagoof.com'),
            'api_key' => env('EG_API_KEY'),
            'enabled' => env('EG_SYNC_ENABLED', true),
        ],
    ],
    
    // Sync settings
    'sync_on_create' => true,
    'sync_on_update' => true,
    'sync_on_delete' => false, // Optional: sync deletions
    
    // Queue settings
    'use_queue' => true,
    'queue_name' => 'product-sync',
    
    // Retry settings
    'max_retries' => 3,
    'retry_delay' => 60, // seconds
    
    // Fields to sync
    'sync_fields' => [
        'name',
        'description',
        'content',
        'sku',
        'price',
        'sale_price',
        'quantity',
        'images',
        'status',
        'is_featured',
        'brand_id',
        'tax_id',
        // ... all product fields
    ],
    
    // Fields to exclude (country-specific)
    'exclude_fields' => [
        'quantity', // May differ per country
        'price',    // May need currency conversion
    ],
    
    // Relationships to sync
    'sync_relationships' => [
        'categories',
        'tags',
        'collections',
        'labels',
        'attributes',
        'variations',
    ],
];
```

#### Step 3: Event Listener

**`src/Listeners/SyncProductListener.php`:**
```php
<?php

namespace Botble\MultiCountrySync\Listeners;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Ecommerce\Models\Product;
use Botble\MultiCountrySync\Services\ProductSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SyncProductListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        protected ProductSyncService $syncService
    ) {
    }

    public function handle(CreatedContentEvent|UpdatedContentEvent $event): void
    {
        // Only sync products
        if ($event->screen !== PRODUCT_MODULE_SCREEN_NAME) {
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

        // Check if product should be synced (you can add conditions)
        if (! $this->shouldSync($product)) {
            return;
        }

        // Sync product to other instances
        if ($event instanceof CreatedContentEvent) {
            $this->syncService->syncProduct($product, 'create');
        } else {
            $this->syncService->syncProduct($product, 'update');
        }
    }

    protected function shouldSync(Product $product): bool
    {
        // Add your logic here
        // For example: only sync published products
        return $product->status === 'published';
    }
}
```

#### Step 4: Sync Service

**`src/Services/ProductSyncService.php`:**
```php
<?php

namespace Botble\MultiCountrySync\Services;

use Botble\Ecommerce\Models\Product;
use Botble\MultiCountrySync\Models\SyncLog;
use Botble\MultiCountrySync\Services\ApiClientService;
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

        foreach ($instances as $instanceKey => $instance) {
            if (! ($instance['enabled'] ?? true)) {
                continue;
            }

            try {
                $this->syncToInstance($product, $instance, $action);
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

    protected function syncToInstance(Product $product, array $instance, string $action): void
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
            'instance' => $instance['url'],
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
        if (config('plugins.multi-country-sync.sync.sync_relationships.categories', false)) {
            $data['categories'] = $product->categories->pluck('id')->toArray();
        }
        
        if (config('plugins.multi-country-sync.sync.sync_relationships.tags', false)) {
            $data['tags'] = $product->tags->pluck('id')->toArray();
        }
        
        // Handle images
        if (isset($data['images'])) {
            $data['images'] = json_decode($data['images'], true) ?: [];
        }
        
        // Add metadata
        $data['sync_metadata'] = [
            'source_instance' => config('app.url'),
            'source_product_id' => $product->id,
            'synced_at' => now()->toIso8601String(),
        ];
        
        return $data;
    }
}
```

#### Step 5: API Client Service

**`src/Services/ApiClientService.php`:**
```php
<?php

namespace Botble\MultiCountrySync\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiClientService
{
    protected int $timeout = 30;
    protected int $maxRetries = 3;

    public function createProduct(string $baseUrl, string $apiKey, array $productData): array
    {
        return $this->makeRequest('POST', "{$baseUrl}/api/sync/products", $productData, $apiKey);
    }

    public function updateProduct(string $baseUrl, string $apiKey, int $productId, array $productData): array
    {
        return $this->makeRequest('PUT', "{$baseUrl}/api/sync/products/{$productId}", $productData, $apiKey);
    }

    public function deleteProduct(string $baseUrl, string $apiKey, int $productId): array
    {
        return $this->makeRequest('DELETE', "{$baseUrl}/api/sync/products/{$productId}", [], $apiKey);
    }

    protected function makeRequest(string $method, string $url, array $data, string $apiKey): array
    {
        $retries = 0;
        
        while ($retries < $this->maxRetries) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders([
                        'Authorization' => "Bearer {$apiKey}",
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ])
                    ->{strtolower($method)}($url, $data);

                if ($response->successful()) {
                    return $response->json();
                }

                if ($response->status() === 404 && $method === 'PUT') {
                    // Product doesn't exist, try creating it
                    return $this->createProduct(
                        parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST),
                        $apiKey,
                        $data
                    );
                }

                throw new Exception("API request failed: {$response->status()} - {$response->body()}");
            } catch (Exception $e) {
                $retries++;
                
                if ($retries >= $this->maxRetries) {
                    throw $e;
                }
                
                // Wait before retry
                sleep(config('plugins.multi-country-sync.sync.retry_delay', 60));
            }
        }

        throw new Exception("Max retries exceeded");
    }
}
```

#### Step 6: Sync Controller (Receiving End)

**`src/Http/Controllers/SyncController.php`:**
```php
<?php

namespace Botble\MultiCountrySync\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
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
            // You can store this in a separate table or as meta
            $product->setMeta('sync_metadata', $data['sync_metadata']);
        }
        
        return $response
            ->setData(['product_id' => $product->id, 'action' => 'created'])
            ->setMessage('Product synced successfully');
    }

    protected function findExistingProduct(array $data): ?Product
    {
        // Try to find by source_product_id from metadata
        if (isset($data['sync_metadata']['source_product_id'])) {
            $product = Product::query()
                ->whereHas('metaBoxes', function ($query) use ($data) {
                    $query->where('meta_key', 'sync_metadata')
                        ->where('meta_value->source_product_id', $data['sync_metadata']['source_product_id']);
                })
                ->first();
            
            if ($product) {
                return $product;
            }
        }
        
        // Try to find by SKU
        if (isset($data['sku'])) {
            return Product::query()->where('sku', $data['sku'])->first();
        }
        
        return null;
    }
}
```

#### Step 7: API Routes

**`routes/api.php`:**
```php
<?php

use Botble\MultiCountrySync\Http\Controllers\SyncController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'auth:sanctum'])->prefix('sync')->group(function () {
    Route::post('products', [SyncController::class, 'syncProduct']);
    Route::put('products/{id}', [SyncController::class, 'syncProduct']);
    Route::delete('products/{id}', [SyncController::class, 'deleteProduct']);
});
```

#### Step 8: Register Event Listeners

**`src/Providers/MultiCountrySyncServiceProvider.php`:**
```php
<?php

namespace Botble\MultiCountrySync\Providers;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\MultiCountrySync\Listeners\SyncProductListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class MultiCountrySyncServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    protected $listen = [
        CreatedContentEvent::class => [
            SyncProductListener::class,
        ],
        UpdatedContentEvent::class => [
            SyncProductListener::class,
        ],
    ];

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/multi-country-sync')
            ->loadAndPublishConfigurations(['sync'])
            ->loadMigrations()
            ->loadRoutes(['api']);
    }
}
```

#### Step 9: Sync Log Model

**`src/Models/SyncLog.php`:**
```php
<?php

namespace Botble\MultiCountrySync\Models;

use Botble\Base\Models\BaseModel;

class SyncLog extends BaseModel
{
    protected $table = 'multi_country_sync_logs';

    protected $fillable = [
        'product_id',
        'instance',
        'action',
        'status',
        'error_message',
        'response_data',
    ];

    protected $casts = [
        'response_data' => 'array',
    ];
}
```

#### Step 10: Migration

**`database/migrations/xxxx_create_sync_logs_table.php`:**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('multi_country_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable();
            $table->string('instance');
            $table->string('action'); // create, update, delete
            $table->string('status'); // success, failed, pending
            $table->text('error_message')->nullable();
            $table->json('response_data')->nullable();
            $table->timestamps();
            
            $table->index('product_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('multi_country_sync_logs');
    }
};
```

---

## Git Branch Workflow

### Understanding the Branch Structure

The Shagoof project uses Git branches to manage code for different countries:

```
eg (main branch)
├── Production: eg.shagoof.com
├── Database: eg_shagoof_db
└── Code: Egypt-specific configurations

uae (branch)
├── Production: uae.shagoof.com
├── Database: uae_shagoof_db
└── Code: UAE-specific configurations

sa (branch)
├── Production: sa.shagoof.com
├── Database: sa_shagoof_db
└── Code: Saudi Arabia-specific configurations
```

### Branch Management Commands

```bash
# Switch to EG branch (main)
git checkout eg

# Switch to UAE branch
git checkout uae

# Switch to SA branch
git checkout sa

# Create new branch from EG
git checkout -b feature-name eg

# Merge changes from EG to UAE
git checkout uae
git merge eg

# Merge changes from EG to SA
git checkout sa
git merge eg
```

### Plugin Development Workflow

1. **Always start in EG branch** (main branch)
2. **Create/update plugin code** in EG branch
3. **Test locally** in EG branch
4. **Commit and push** to EG branch
5. **Merge to other branches** (UAE, SA) when ready
6. **Deploy to production** servers

### Important Notes

- **Plugin code should be identical** across all branches
- **Only `.env` files differ** between branches
- **Database structures** should be identical (same migrations)
- **Configuration values** differ per country (URLs, API keys, etc.)

---

## Setup Instructions

### Step 1: Install Plugin (Start in EG Branch)

**Important**: Always start plugin development in the EG branch!

1. **Switch to EG branch**:
   ```bash
   git checkout eg
   ```

2. **Create the plugin directory structure**:
   ```bash
   mkdir -p platform/plugins/multi-country-sync
   ```

3. **Copy all plugin files** to the directory

4. **Run composer dump-autoload**:
   ```bash
   composer dump-autoload
   ```

5. **Commit to EG branch**:
   ```bash
   git add platform/plugins/multi-country-sync
   git commit -m "Add multi-country sync plugin"
   git push origin eg
   ```

6. **Merge to other branches**:
   ```bash
   # Merge to UAE
   git checkout uae
   git merge eg
   git push origin uae
   
   # Merge to SA
   git checkout sa
   git merge eg
   git push origin sa
   
   # Return to EG branch
   git checkout eg
   ```

7. **Activate plugin in admin panel** on each production server:
   - EG: https://eg.shagoof.com/admin/plugins
   - UAE: https://uae.shagoof.com/admin/plugins
   - SA: https://sa.shagoof.com/admin/plugins

### Step 2: Configure Environment Variables

**`.env` file for each country:**

```env
# ============================================
# EG Branch (eg.shagoof.com)
# ============================================
CURRENT_COUNTRY=eg
MULTI_COUNTRY_SYNC_ENABLED=true

# UAE Instance
UAE_API_URL=https://uae.shagoof.com
UAE_API_KEY=your-uae-api-key-here
UAE_SYNC_ENABLED=true

# SA Instance
SA_API_URL=https://sa.shagoof.com
SA_API_KEY=your-sa-api-key-here
SA_SYNC_ENABLED=true

# ============================================
# UAE Branch (uae.shagoof.com)
# ============================================
CURRENT_COUNTRY=uae
MULTI_COUNTRY_SYNC_ENABLED=true

# EG Instance
EG_API_URL=https://eg.shagoof.com
EG_API_KEY=your-eg-api-key-here
EG_SYNC_ENABLED=true

# SA Instance
SA_API_URL=https://sa.shagoof.com
SA_API_KEY=your-sa-api-key-here
SA_SYNC_ENABLED=true

# ============================================
# SA Branch (sa.shagoof.com)
# ============================================
CURRENT_COUNTRY=sa
MULTI_COUNTRY_SYNC_ENABLED=true

# EG Instance
EG_API_URL=https://eg.shagoof.com
EG_API_KEY=your-eg-api-key-here
EG_SYNC_ENABLED=true

# UAE Instance
UAE_API_URL=https://uae.shagoof.com
UAE_API_KEY=your-uae-api-key-here
UAE_SYNC_ENABLED=true
```

### Step 3: Generate API Keys

Create API tokens for each instance:

```php
// In each country's installation
php artisan tinker

$token = \Laravel\Sanctum\PersonalAccessToken::create([
    'name' => 'Multi-Country Sync',
    'token' => hash('sha256', Str::random(40)),
    'abilities' => ['sync:products'],
]);
```

### Step 4: Set Up Queue Worker

Since we're using queues, set up queue workers:

```bash
php artisan queue:work --queue=product-sync
```

Or use supervisor for production.

---

## Advanced Features

### 1. Conflict Resolution

Handle cases where product already exists:

```php
// In SyncController
protected function resolveConflict(Product $existing, array $newData): Product
{
    // Strategy 1: Use source timestamp
    if ($existing->updated_at < $newData['updated_at']) {
        return $this->updateProduct($existing, $newData);
    }
    
    // Strategy 2: Manual review flag
    $existing->setMeta('needs_review', true);
    
    // Strategy 3: Merge (complex)
    // ...
}
```

### 2. Selective Sync

Add conditions for which products to sync:

```php
// In SyncProductListener
protected function shouldSync(Product $product): bool
{
    // Only sync if product has specific tag
    if ($product->tags->contains('name', 'sync-all-countries')) {
        return true;
    }
    
    // Don't sync if product has "local-only" tag
    if ($product->tags->contains('name', 'local-only')) {
        return false;
    }
    
    return true;
}
```

### 3. Currency Conversion

Convert prices based on exchange rates:

```php
protected function prepareProductData(Product $product): array
{
    $data = parent::prepareProductData($product);
    
    // Convert price if needed
    if (isset($data['price'])) {
        $data['price'] = $this->convertCurrency(
            $data['price'],
            $this->getSourceCurrency(),
            $this->getTargetCurrency()
        );
    }
    
    return $data;
}
```

### 4. Image Synchronization

Handle product images:

```php
protected function syncImages(array $images, string $targetInstance): array
{
    $syncedImages = [];
    
    foreach ($images as $image) {
        // Download image from source
        $imageContent = Http::get($image)->body();
        
        // Upload to target instance
        $uploadedImage = $this->apiClient->uploadImage($targetInstance, $imageContent);
        
        $syncedImages[] = $uploadedImage['url'];
    }
    
    return $syncedImages;
}
```

### 5. Relationship Mapping

Map relationships (categories, brands) between instances:

```php
protected function mapCategoryIds(array $categoryIds, string $targetInstance): array
{
    $mappedIds = [];
    
    foreach ($categoryIds as $categoryId) {
        // Get category from source
        $category = ProductCategory::find($categoryId);
        
        // Find or create in target
        $targetCategory = $this->findOrCreateCategory($category, $targetInstance);
        
        $mappedIds[] = $targetCategory['id'];
    }
    
    return $mappedIds;
}
```

---

## Testing

### Test Sync Flow

1. **Switch to EG branch**: `git checkout eg`
2. Create product in EG (eg.shagoof.com)
3. Check sync logs in admin panel
4. Verify product appears in UAE (uae.shagoof.com) and SA (sa.shagoof.com)
5. Update product in EG
6. Verify updates sync to UAE and SA

**To test from other countries:**
- Switch to UAE branch: `git checkout uae`
- Create/update product in UAE
- Verify sync to EG and SA

### Test Error Handling

1. Disable one instance
2. Create product
3. Verify retry mechanism works
4. Re-enable instance
5. Verify sync completes

---

## Monitoring & Logging

### Sync Dashboard

Create admin panel to view sync status:

- List of synced products
- Sync success/failure rates
- Last sync time per instance
- Retry queue status

### Alerts

Set up alerts for:
- Sync failures
- High failure rates
- Queue backlog
- API timeouts

---

## Security Considerations

1. **API Authentication**: Use secure API keys (Sanctum tokens)
2. **HTTPS Only**: Always use HTTPS for API calls
3. **Rate Limiting**: Implement rate limiting on sync endpoints
4. **IP Whitelisting**: Optionally whitelist IPs of other instances
5. **Data Validation**: Validate all incoming sync data
6. **Audit Logging**: Log all sync operations

---

## Alternative: Database-Level Replication

If you want to avoid API calls, consider:

1. **Master-Slave Replication**: One master DB, others replicate
2. **Multi-Master Replication**: All DBs replicate to each other
3. **Shared Database**: One database for all instances (latency issues)

**Note**: Database replication is complex and may not work well across countries due to latency.

---

## Recommended Approach

**Use Event-Driven API Synchronization** because:
- ✅ Works across different servers/countries
- ✅ Flexible and extensible
- ✅ Can handle failures gracefully
- ✅ Real-time synchronization
- ✅ Can add features like selective sync
- ✅ Easy to monitor and debug

---

## Next Steps

### Implementation Workflow

1. **Start with EG branch** (main branch):
   ```bash
   git checkout eg
   ```

2. **Create the sync plugin** in EG branch following the structure above

3. **Commit and push to EG branch**:
   ```bash
   git add .
   git commit -m "Add multi-country sync plugin"
   git push origin eg
   ```

4. **Merge plugin to UAE and SA branches**:
   ```bash
   # Merge to UAE
   git checkout uae
   git merge eg
   git push origin uae
   
   # Merge to SA
   git checkout sa
   git merge eg
   git push origin sa
   ```

5. **Configure environment variables** on each production server:
   - EG: Set EG-specific `.env` values
   - UAE: Set UAE-specific `.env` values
   - SA: Set SA-specific `.env` values

6. **Set up API endpoints** on all 3 production instances

7. **Generate API keys** for each instance

8. **Test with one product** from EG

9. **Monitor sync logs** in admin panel

10. **Gradually enable for all products**

### Branch-Specific Configuration

**Important**: The plugin code should be identical across all branches. Only `.env` configuration differs:
- Each branch has its own `.env` file
- Each branch points to different production URLs
- Each branch has different API keys

### Deployment Checklist

- [ ] Plugin created in EG branch
- [ ] Plugin merged to UAE branch
- [ ] Plugin merged to SA branch
- [ ] Environment variables configured on all 3 servers
- [ ] API keys generated for all instances
- [ ] Queue workers running on all servers
- [ ] Test sync from EG → UAE & SA
- [ ] Test sync from UAE → EG & SA
- [ ] Test sync from SA → EG & UAE
- [ ] Monitor sync logs
- [ ] Enable for production use

Would you like me to create the complete plugin code files for you?

