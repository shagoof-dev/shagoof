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
        $maxRetries = config('plugins.multi-country-sync.sync.max_retries', $this->maxRetries);
        $retryDelay = config('plugins.multi-country-sync.sync.retry_delay', 60);
        
        while ($retries < $maxRetries) {
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

