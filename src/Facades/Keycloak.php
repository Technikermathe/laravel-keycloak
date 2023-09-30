<?php

namespace Technikermathe\Keycloak\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Technikermathe\Keycloak\Keycloak
 */
class Keycloak extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Technikermathe\Keycloak\Keycloak::class;
    }
}
