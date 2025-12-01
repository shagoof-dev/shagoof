<?php

namespace Botble\MultiCountrySync\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiClientService
{
    protected int $timeout;
    protected int $maxRetries = 3;

    public function __construct()
    {
        // Set timeout from config, default to 300 seconds (5 minutes) for image-heavy syncs
        $this->timeout = (int) config('plugins.multi-country-sync.sync.api_timeout', 300);
    }

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
        $maxRetries = config('plugins.multi-country-sync.sync.max_retries', $this->maxRetries);
        $retryDelay = config('plugins.multi-country-sync.sync.retry_delay', 60);
        
        // Get timeout from config (may have been updated)
        $timeout = config('plugins.multi-country-sync.sync.api_timeout', $this->timeout);
        
        // Check if request has images - increase timeout for image-heavy requests
        $hasImages = isset($data['images']) && !empty($data['images']);
        if ($hasImages) {
            // Add extra time per image (30 seconds per image, minimum 5 minutes)
            $imageCount = count($data['images']);
            $calculatedTimeout = max($timeout, 300 + ($imageCount * 30));
            Log::debug('Multi-Country Sync API: Image-heavy request detected', [
                'image_count' => $imageCount,
                'base_timeout' => $timeout,
                'calculated_timeout' => $calculatedTimeout,
            ]);
            $timeout = $calculatedTimeout;
        }
        
        Log::debug('Multi-Country Sync API: Making request', [
            'method' => $method,
            'url' => $url,
            'timeout' => $timeout,
            'has_images' => $hasImages,
        ]);
        
        while ($retries < $maxRetries) {
            try {
                $response = Http::timeout($timeout)
                    ->connectTimeout(30) // Connection timeout separate from request timeout
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
                    $baseUrl = parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST);
                    return $this->createProduct($baseUrl, $apiKey, $data);
                }

                throw new Exception("API request failed: {$response->status()} - {$response->body()}");
            } catch (Exception $e) {
                $retries++;
                
                if ($retries >= $maxRetries) {
                    throw $e;
                }
                
                Log::warning("Sync retry {$retries}/{$maxRetries} for {$url}: " . $e->getMessage());
                
                // Wait before retry
                sleep($retryDelay);
            }
        }

        throw new Exception("Max retries exceeded");
    }
}

