<?php

namespace FleetCart\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;

class CustomJWTAuth
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('api/*')) {            
            try {
                $user = JWTAuth::parseToken()->authenticate();
                if (!$user) {
                    return response()->json(['error' => 'User not found.'], 404);
                }
                
                $request->setUserResolver(function () use ($user) {
                    return $user;
                });

            } catch (\Exception $e) {
                /* \Log::error('JWTAuth Error: ' . $e->getMessage());
                return response()->json(['error' => 'Unauthorized.'], 401); */
            }
        }
        return $next($request);
    }    
}
