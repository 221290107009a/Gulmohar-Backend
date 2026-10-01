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
                'https://sanghoapp.phxsolution.com',       
                'https://sanghostage.phxsolution.com',
                'https://www.sangho.app',
                'https://sangho.app',
                'http://localhost:3000',
                'https://sangho-app-next.vercel.app',
                'http://43.205.208.46:3000',
                'http://sangho-backend.local',
                'https://web.sangho.app'
            ];

            // Get the Origin header from the request
            $origin = $request->headers->get('Origin');

            // Check if the origin is allowed and set the appropriate header
            if (in_array($origin, $allowedOrigins)) {
                $response->headers->set('Access-Control-Allow-Origin', $origin);
            }

            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Authorization, Content-Type, Accept, random_number, accesskey');

            return $response;
        }

        return $next($request);
    }
}
