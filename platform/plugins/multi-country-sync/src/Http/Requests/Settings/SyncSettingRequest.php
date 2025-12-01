<?php

namespace Botble\MultiCountrySync\Http\Requests\Settings;

use Botble\Support\Http\Requests\Request;

class SyncSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'multi_country_sync_enabled' => 'nullable|boolean',
            'current_country' => 'required|string|in:eg,uae,sa',
            'uae_api_url' => 'nullable|url',
            'uae_api_key' => 'nullable|string|max:255',
            'uae_sync_enabled' => 'nullable|boolean',
            'sa_api_url' => 'nullable|url',
            'sa_api_key' => 'nullable|string|max:255',
            'sa_sync_enabled' => 'nullable|boolean',
            'eg_api_url' => 'nullable|url',
            'eg_api_key' => 'nullable|string|max:255',
            'eg_sync_enabled' => 'nullable|boolean',
            'sync_on_create' => 'nullable|boolean',
            'sync_on_update' => 'nullable|boolean',
            'use_queue' => 'nullable|boolean',
            'max_retries' => 'nullable|integer|min:1|max:10',
            'retry_delay' => 'nullable|integer|min:1|max:300',
        ];
    }
}

