<?php

namespace Technikermathe\Keycloak\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Technikermathe\Keycloak\Data\IdToken;

class KeycloakPersistentUserProvider implements UserProvider
{
    public function __construct(protected string $model)
    {
    }

    public function retrieveById($identifier)
    {
        return $this->model::find($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        throw new \BadMethodCallException();
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        throw new \BadMethodCallException();
    }

    /**
     * @return Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        $idToken = IdToken::from($credentials);

        return $this->model::updateOrCreate([
            'id' => $idToken->sub,
        ], [
            'name' => $idToken->name,
            'email' => $idToken->email,
            'givenName' => $idToken->given_name,
            'familyName' => $idToken->family_name,
        ]);
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        throw new \BadMethodCallException();
    }
}
