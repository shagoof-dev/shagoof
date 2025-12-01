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

        // Save settings with prefix
        $settings = [];
        foreach ($data as $key => $value) {
            $settings[$key] = $value;
        }

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

