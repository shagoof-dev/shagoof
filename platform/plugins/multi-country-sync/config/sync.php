<?php

use Botble\Setting\Facades\Setting;

// Helper function to get setting with fallback to env
$getSetting = function (string $key, $default = null) {
    return setting($key, env(strtoupper(str_replace('.', '_', $key)), $default));
};

return [
    'enabled' => $getSetting('multi_country_sync_enabled', false),
    
    // Current country identifier (eg, uae, sa)
    'current_country' => $getSetting('multi_country_sync_current_country', env('CURRENT_COUNTRY', 'eg')),
    
    // Other country instances
    'instances' => [
        'uae' => [
            'url' => $getSetting('multi_country_sync_uae_api_url', env('UAE_API_URL', 'https://uae.shagoof.com')),
            'api_key' => $getSetting('multi_country_sync_uae_api_key', env('UAE_API_KEY')),
            'enabled' => $getSetting('multi_country_sync_uae_enabled', env('UAE_SYNC_ENABLED', true)),
        ],
        'sa' => [
            'url' => $getSetting('multi_country_sync_sa_api_url', env('SA_API_URL', 'https://sa.shagoof.com')),
            'api_key' => $getSetting('multi_country_sync_sa_api_key', env('SA_API_KEY')),
            'enabled' => $getSetting('multi_country_sync_sa_enabled', env('SA_SYNC_ENABLED', true)),
        ],
        'eg' => [
            'url' => $getSetting('multi_country_sync_eg_api_url', env('EG_API_URL', 'https://eg.shagoof.com')),
            'api_key' => $getSetting('multi_country_sync_eg_api_key', env('EG_API_KEY')),
            'enabled' => $getSetting('multi_country_sync_eg_enabled', env('EG_SYNC_ENABLED', true)),
        ],
    ],
    
    // Sync settings
    'sync_on_create' => $getSetting('multi_country_sync_on_create', true),
    'sync_on_update' => $getSetting('multi_country_sync_on_update', true),
    'sync_on_delete' => false, // Optional: sync deletions
    
    // Queue settings
    'use_queue' => $getSetting('multi_country_sync_use_queue', true),
    'queue_name' => 'product-sync',
    
    // Retry settings
    'max_retries' => (int) $getSetting('multi_country_sync_max_retries', 3),
    'retry_delay' => (int) $getSetting('multi_country_sync_retry_delay', 60), // seconds
    
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
        'weight',
        'length',
        'wide',
        'height',
        'barcode',
        'cost_per_item',
        'minimum_order_quantity',
        'maximum_order_quantity',
        'with_storehouse_management',
        'allow_checkout_when_out_of_stock',
        'stock_status',
        'sale_type',
        'start_date',
        'end_date',
        'product_type',
        'generate_license_code',
        'license_code_type',
        'specification_table_id',
    ],
    
    // Fields to exclude (country-specific)
    'exclude_fields' => [
        // 'quantity', // May differ per country
        // 'price',    // May need currency conversion
    ],
    
    // Relationships to sync
    'sync_relationships' => [
        'categories' => true,
        'tags' => true,
        'collections' => true,
        'labels' => true,
        'attributes' => true,
        'variations' => true,
        'taxes' => true,
    ],
];

