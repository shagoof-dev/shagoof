<?php

namespace Botble\MultiCountrySync\Http\Controllers\Settings;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\MultiCountrySync\Forms\Settings\SyncSettingForm;
use Botble\MultiCountrySync\Http\Requests\Settings\SyncSettingRequest;
use Botble\Setting\Facades\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SyncSettingController extends BaseController
{
    public function edit()
    {
        $this->pageTitle(trans('plugins/multi-country-sync::sync.settings.title'));

        return SyncSettingForm::create()->renderForm();
    }

    public function update(SyncSettingRequest $request, BaseHttpResponse $response)
    {
        $data = $request->validated();

        // Map form field names to setting keys
        $settings = [];
        
        // Main settings
        if (isset($data['multi_country_sync_enabled'])) {
            $settings['multi_country_sync_enabled'] = $data['multi_country_sync_enabled'] ? '1' : '0';
        }
        
        // Current country - always save if present in request
        $currentCountry = $request->input('current_country') ?? $request->input('multi_country_sync_current_country');
        if ($currentCountry && in_array($currentCountry, ['eg', 'uae', 'sa'])) {
            $settings['multi_country_sync_current_country'] = (string) $currentCountry;
        }
        
        // UAE settings
        if (isset($data['uae_api_url'])) {
            $settings['multi_country_sync_uae_api_url'] = $data['uae_api_url'];
        }
        if (isset($data['uae_api_key'])) {
            $settings['multi_country_sync_uae_api_key'] = $data['uae_api_key'];
        }
        if (isset($data['uae_sync_enabled'])) {
            $settings['multi_country_sync_uae_enabled'] = $data['uae_sync_enabled'] ? '1' : '0';
        }
        
        // SA settings
        if (isset($data['sa_api_url'])) {
            $settings['multi_country_sync_sa_api_url'] = $data['sa_api_url'];
        }
        if (isset($data['sa_api_key'])) {
            $settings['multi_country_sync_sa_api_key'] = $data['sa_api_key'];
        }
        if (isset($data['sa_sync_enabled'])) {
            $settings['multi_country_sync_sa_enabled'] = $data['sa_sync_enabled'] ? '1' : '0';
        }
        
        // EG settings
        if (isset($data['eg_api_url'])) {
            $settings['multi_country_sync_eg_api_url'] = $data['eg_api_url'];
        }
        if (isset($data['eg_api_key'])) {
            $settings['multi_country_sync_eg_api_key'] = $data['eg_api_key'];
        }
        if (isset($data['eg_sync_enabled'])) {
            $settings['multi_country_sync_eg_enabled'] = $data['eg_sync_enabled'] ? '1' : '0';
        }
        
        // Sync options
        if (isset($data['sync_on_create'])) {
            $settings['multi_country_sync_on_create'] = $data['sync_on_create'] ? '1' : '0';
        }
        if (isset($data['sync_on_update'])) {
            $settings['multi_country_sync_on_update'] = $data['sync_on_update'] ? '1' : '0';
        }
        if (isset($data['use_queue'])) {
            $settings['multi_country_sync_use_queue'] = $data['use_queue'] ? '1' : '0';
        }
        if (isset($data['max_retries'])) {
            $settings['multi_country_sync_max_retries'] = (string) $data['max_retries'];
        }
        if (isset($data['retry_delay'])) {
            $settings['multi_country_sync_retry_delay'] = (string) $data['retry_delay'];
        }

        // Save all settings
        Setting::set($settings)->save();

        return $response
            ->setPreviousUrl(route('multi-country-sync.settings'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function generateApiKey(Request $request, BaseHttpResponse $response)
    {
        $instance = $request->input('instance'); // uae, sa, or eg
        
        if (! in_array($instance, ['uae', 'sa', 'eg'])) {
            return $response
                ->setError()
                ->setMessage('Invalid instance')
                ->setStatusCode(400);
        }

        // Generate a secure API key (64 characters)
        $apiKey = Str::random(64);
        
        // Save the API key to settings
        $settingKey = "multi_country_sync_{$instance}_api_key";
        Setting::set($settingKey, $apiKey)->save();

        return $response
            ->setData([
                'api_key' => $apiKey,
                'instance' => strtoupper($instance),
            ])
            ->setMessage(trans('plugins/multi-country-sync::sync.settings.api_key_generated'));
    }
}

