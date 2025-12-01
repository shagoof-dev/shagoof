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

    /**
     * Test connection from admin panel (internal)
     */
    public function test(Request $request, BaseHttpResponse $response)
    {
        $instance = $request->input('instance'); // eg, uae, sa
        
        if (empty($instance)) {
            return $response
                ->setError()
                ->setMessage('Instance parameter is required')
                ->setStatusCode(400);
        }

        $instances = config('plugins.multi-country-sync.sync.instances', []);

        if (! isset($instances[$instance])) {
            return $response
                ->setError()
                ->setMessage('Invalid instance: ' . $instance)
                ->setStatusCode(400);
        }

        $instanceConfig = $instances[$instance];
        
        // Check if instance is enabled
        if (! ($instanceConfig['enabled'] ?? true)) {
            return $response
                ->setData([
                    'status' => 'skipped',
                    'message' => 'Instance is disabled',
                    'url' => $instanceConfig['url'] ?? '',
                ])
                ->setMessage('Instance is disabled');
        }

        // Check if URL and API key are configured
        if (empty($instanceConfig['url']) || empty($instanceConfig['api_key'])) {
            return $response
                ->setData([
                    'status' => 'skipped',
                    'message' => 'URL or API Key not configured',
                    'url' => $instanceConfig['url'] ?? '',
                ])
                ->setMessage('URL or API Key not configured');
        }

        try {
            // Try to make a simple request to test connection
            // Use the sync API endpoint with a test request
            $testUrl = rtrim($instanceConfig['url'], '/') . '/api/sync/test-connection';
            
            $httpResponse = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . ($instanceConfig['api_key'] ?? ''),
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($testUrl, [
                    'test' => true,
                ]);

            if ($httpResponse->successful()) {
                return $response
                    ->setData([
                        'status' => 'success',
                        'message' => 'Connection successful',
                        'url' => $instanceConfig['url'],
                    ])
                    ->setMessage('Connection test successful');
            }

            // Check if it's an authentication error (401/403)
            if ($httpResponse->status() === 401 || $httpResponse->status() === 403) {
                return $response
                    ->setError()
                    ->setData([
                        'status' => 'error',
                        'message' => 'Authentication failed - Invalid API key',
                        'url' => $instanceConfig['url'],
                        'status_code' => $httpResponse->status(),
                    ])
                    ->setMessage('Authentication failed - Invalid API key');
            }

            // For other errors, try a simple GET request to check if server is reachable
            $simpleTestUrl = rtrim($instanceConfig['url'], '/');
            $simpleResponse = \Illuminate\Support\Facades\Http::timeout(5)->get($simpleTestUrl);
            
            if ($simpleResponse->successful() || $simpleResponse->status() < 500) {
                return $response
                    ->setData([
                        'status' => 'success',
                        'message' => 'Server is reachable (endpoint may not exist)',
                        'url' => $instanceConfig['url'],
                    ])
                    ->setMessage('Server is reachable');
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
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $response
                ->setError()
                ->setData([
                    'status' => 'error',
                    'message' => 'Connection timeout or server unreachable',
                    'error' => $e->getMessage(),
                    'url' => $instanceConfig['url'] ?? '',
                ])
                ->setMessage('Connection timeout or server unreachable: ' . $e->getMessage());
        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setData([
                    'status' => 'error',
                    'message' => 'Connection failed',
                    'error' => $e->getMessage(),
                    'url' => $instanceConfig['url'] ?? '',
                ])
                ->setMessage('Connection test failed: ' . $e->getMessage());
        }
    }

    /**
     * Test connection API endpoint (called by other instances)
     */
    public function testApi(Request $request, BaseHttpResponse $response)
    {
        // This endpoint is called by other instances to test connectivity
        // It requires API key authentication via middleware
        return $response
            ->setData([
                'status' => 'success',
                'message' => 'Connection test successful',
                'timestamp' => now()->toIso8601String(),
            ])
            ->setMessage('Connection test successful');
    }
}

