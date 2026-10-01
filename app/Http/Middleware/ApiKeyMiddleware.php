<?php

namespace FleetCart\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('api/*')) {
            $apiKey = $request->header('accesskey');
            if (!$request->is('api/confirm') && !$request->is('api/analytics/*') && !$request->is('api/app-sync/*') && !$request->is('api/books/audio/stream/*')) {                
                if ($apiKey !== env('API_KEY')) {                 
                    return response()->json(['error' => 'Unauthorized'], 401);
                }
            }
        }        
        return $next($request);        
    }
}
