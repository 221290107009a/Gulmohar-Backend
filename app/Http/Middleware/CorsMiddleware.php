<?php

namespace FleetCart\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->is('api/*')) {
            if ($request->getMethod() === "OPTIONS") {
                $response = response('', 200);
            } else {
                $response = $next($request);
            }

            // Define allowed origins
            $allowedOrigins = [
                'http://localhost:3000',
                'http://localhost:3001',
                'http://127.0.0.1:3001',
            ];
            $webAppUrl = rtrim((string) config('app.web_app_url'), '/');
            if ($webAppUrl !== '') {
                $allowedOrigins[] = $webAppUrl;
            }

            // Get the Origin header from the request
            $origin = $request->headers->get('Origin');

            // Check if the origin is allowed and set the appropriate header
            if (in_array($origin, $allowedOrigins)) {
                $response->headers->set('Access-Control-Allow-Origin', $origin);
            }

            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Authorization, Content-Type, Accept, random_number, accesskey, X-User-Id, *');

            return $response;
        }

        return $next($request);
    }
}
