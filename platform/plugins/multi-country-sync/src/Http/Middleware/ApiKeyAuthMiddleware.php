<?php

namespace Botble\MultiCountrySync\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->bearerToken() ?: $request->header('X-API-Key');
        
        if (! $apiKey) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'API key is required',
            ], 401);
        }
        
        $instances = config('plugins.multi-country-sync.sync.instances', []);
        $currentCountry = config('plugins.multi-country-sync.sync.current_country', 'eg');
        
        // Get the current instance's API key (the key for THIS instance)
        $currentInstanceApiKey = $instances[$currentCountry]['api_key'] ?? null;
        
        if (empty($currentInstanceApiKey)) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'API key not configured for this instance',
            ], 401);
        }
        
        // Check if the incoming API key matches THIS instance's API key
        // Other instances will send THIS instance's API key when syncing to us
        if ($apiKey !== $currentInstanceApiKey) {
            \Illuminate\Support\Facades\Log::warning('Multi-Country Sync: Invalid API key', [
                'current_country' => $currentCountry,
                'expected_key_length' => strlen($currentInstanceApiKey),
                'received_key_length' => strlen($apiKey),
                'keys_match' => $apiKey === $currentInstanceApiKey,
            ]);
            
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid API key',
            ], 401);
        }
        
        return $next($request);
    }
}

