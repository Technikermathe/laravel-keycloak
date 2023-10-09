<?php

namespace Technikermathe\Keycloak\Middleware;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class Can extends Authenticated
{
    public function handle($request, Closure $next, ...$guards)
    {
        if (empty($guards) && Auth::check()) {
            return $next($request);
        }

        $role = Arr::first(Arr::wrap($guards));

        if (Auth::hasRole($role)) {
            return $next($request);
        }

        abort(403);
    }
}
