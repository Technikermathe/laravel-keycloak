<?php

namespace Technikermathe\Keycloak\Middleware;

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;

class Authenticated extends Authenticate
{
    protected function redirectTo(Request $request)
    {
        return route('login');
    }

}
