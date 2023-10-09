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

        $user = $this->model::firstOrNew(['id' => $idToken->sub]);

        $user->name = $idToken->name;
        $user->email = $idToken->email;
        $user->givenName = $idToken->given_name;
        $user->familyName = $idToken->family_name;

        $user->save();

        return $user;
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        throw new \BadMethodCallException();
    }
}
