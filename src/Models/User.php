<?php

namespace Technikermathe\Keycloak\Models;

use BadMethodCallException;
use Illuminate\Contracts\Auth\Authenticatable;

class User implements Authenticatable
{
    public function getAuthIdentifierName(): string
    {
        return 'email';
    }

    public function getAuthIdentifier()
    {
        /** @phpstan-ignore-next-line  */
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
}
