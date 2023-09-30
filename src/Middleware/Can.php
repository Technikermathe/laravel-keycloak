<?php

namespace Technikermathe\Keycloak\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Can extends Authenticated
{
    public function handle($request, Closure $next, ...$guards)
    {
        if (empty($guards) && Auth::check()) {
            return $next($request);
        }

        $guards = explode('|', ($guards[0] ?? ''));
        if (Auth::hasRole($guards)) {
            return $next($request);
        }

        abort(403);
    }
}
