<?php

namespace Technikermathe\Keycloak\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Technikermathe\Keycloak\Data\UserInfo;

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
        $userInfo = UserInfo::from($credentials);

        /** @var Model $class */
        $class = '\\'.ltrim($this->model, '\\');

        DB::transaction(function () use ($userInfo, $class) {
            $class->newQuery()->updateOrInsert([
                'id' => $userInfo->sub,
            ], [
                'name' => $userInfo->name,
                'email' => $userInfo->email,
                'givenName' => $userInfo->given_name,
                'familyName' => $userInfo->family_name,
            ]);
        });

        return $class->newQuery()->find($userInfo->sub);
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        throw new \BadMethodCallException();
    }
}
