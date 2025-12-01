<?php

namespace Botble\MultiCountrySync\Http\Controllers\Api;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\MultiCountrySync\Services\ApiClientService;
use Illuminate\Http\Request;

class TestConnectionController extends BaseController
{
    public function __construct(
        protected ApiClientService $apiClient
    ) {
    }

    public function test(Request $request, BaseHttpResponse $response)
    {
        $instance = $request->input('instance'); // eg, uae, sa
        $instances = config('plugins.multi-country-sync.sync.instances', []);

        if (! isset($instances[$instance])) {
            return $response
                ->setError()
                ->setMessage('Invalid instance')
                ->setStatusCode(400);
        }

        $instanceConfig = $instances[$instance];

        try {
            // Try to make a simple request to test connection
            // We'll use a test endpoint or just check if the URL is reachable
            $testUrl = rtrim($instanceConfig['url'], '/') . '/api/sync/test';
            
            $httpResponse = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . ($instanceConfig['api_key'] ?? ''),
                    'Accept' => 'application/json',
                ])
                ->get($testUrl);

            if ($httpResponse->successful() || $httpResponse->status() === 404) {
                // 404 is OK, it means the server is reachable but endpoint doesn't exist
                return $response
                    ->setData([
                        'status' => 'success',
                        'message' => 'Connection successful',
                        'url' => $instanceConfig['url'],
                    ])
                    ->setMessage('Connection test successful');
            }

            return $response
                ->setError()
                ->setData([
                    'status' => 'error',
                    'message' => 'Connection failed',
                    'error' => $httpResponse->body(),
                    'status_code' => $httpResponse->status(),
                ])
                ->setMessage('Connection test failed: ' . $httpResponse->status());
        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setData([
                    'status' => 'error',
                    'message' => 'Connection failed',
                    'error' => $e->getMessage(),
                ])
                ->setMessage('Connection test failed: ' . $e->getMessage());
        }
    }
}

