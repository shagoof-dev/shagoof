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
        
        // Check if API key matches any instance
        $validKey = false;
        foreach ($instances as $instanceKey => $instance) {
            if ($instanceKey !== $currentCountry && ($instance['api_key'] ?? null) === $apiKey) {
                $validKey = true;
                break;
            }
        }
        
        if (! $validKey) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid API key',
            ], 401);
        }
        
        return $next($request);
    }
}

