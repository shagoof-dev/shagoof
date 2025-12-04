<?php

namespace Botble\MultiCountrySync\Http\Requests;

use Botble\Support\Http\Requests\Request;

class SyncProductRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'sku' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
            'status' => 'nullable|string|in:published,draft,pending',
            'is_featured' => 'nullable|boolean',
            'brand_id' => 'nullable|integer|exists:ec_brands,id',
            'tax_id' => 'nullable|integer|exists:ec_taxes,id',
            'images' => 'nullable|array',
            'categories' => 'nullable|array',
            'tags' => 'nullable|array',
            'product_collections' => 'nullable|array',
            'product_labels' => 'nullable|array',
            'taxes' => 'nullable|array',
            'sync_metadata' => 'nullable|array',
        ];
    }
}

