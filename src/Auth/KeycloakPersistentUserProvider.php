<?php

namespace Technikermathe\Keycloak\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Technikermathe\Keycloak\Data\Token;

class KeycloakPersistentUserProvider implements UserProvider
{
    public function __construct(protected string $model)
    {
    }

    public function retrieveById($identifier)
    {
        /** @var Model $class */
        $class = '\\'.ltrim($this->model, '\\');

        return $class->newQuery()->find($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        throw new \BadMethodCallException();
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        throw new \BadMethodCallException();
    }

    public function retrieveByCredentials(array $credentials)
    {
        $idToken = Token::from($credentials)->getIdToken();

        /** @var Model $class */
        $class = '\\'.ltrim($this->model, '\\');

        DB::transaction(function () use ($idToken, $class) {
            $class->newQuery()->updateOrInsert([
                'id' => $idToken->sub,
            ], [
                'name' => $idToken->name,
                'email' => $idToken->email,
                'givenName' => $idToken->given_name,
                'familyName' => $idToken->family_name,
            ]);
        });

        return $class->newQuery()->find($idToken->sub);
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        throw new \BadMethodCallException();
    }
}
