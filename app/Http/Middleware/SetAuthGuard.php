<?php

namespace FleetCart\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;

class SetAuthGuard
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
            Config::set('auth.defaults.guard', 'api');
        } else {
            Config::set('auth.defaults.guard', 'web');
        }

        return $next($request);
    }
}
