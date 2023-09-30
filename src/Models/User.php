<?php

namespace Technikermathe\Keycloak\Models;

use BadMethodCallException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class User implements Authenticatable
{

    public function getAuthIdentifierName()
    {
        return 'email';
    }

    public function getAuthIdentifier()
    {
        return $this->email;
    }

    public function getAuthPassword()
    {
        throw new BadMethodCallException('Not Implemented.');
    }

    public function getRememberToken()
    {
        throw new BadMethodCallException('Not Implemented.');
    }

    public function setRememberToken($value)
    {
        throw new BadMethodCallException('Not Implemented.');
    }

    public function getRememberTokenName()
    {
        throw new BadMethodCallException('Not Implemented.');
    }

    /**
     * Check user has roles
     *
     * @see KeycloakWebGuard::hasRole()
     *
     * @param  string|array  $roles
     * @param  string  $resource
     * @return boolean
     */
    public function hasRole($roles, $resource = '')
    {
        return Auth::hasRole($roles, $resource);
    }

}
