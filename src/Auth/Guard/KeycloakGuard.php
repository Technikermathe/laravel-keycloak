<?php

namespace Technikermathe\Keycloak\Auth\Guard;

use Illuminate\Support\Facades\Log;
use Technikermathe\Keycloak\Data\Token;
use Technikermathe\Keycloak\Facades\Keycloak;
use BadMethodCallException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Technikermathe\Keycloak\Models\User;
use Throwable;

class KeycloakGuard implements Guard
{
    /**
     * @var null|Authenticatable|User
     */
    protected $user;

    /**
     * Constructor.
     *
     * @param Request $request
     */
    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;
        $this->request = $request;
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        return (bool) $this->user();
    }

    public function hasUser()
    {
        return (bool) $this->user();
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {
        return ! $this->check();
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if (empty($this->user)) {
            $this->authenticate();
        }

        return $this->user;
    }

    /**
     * Set the current user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return void
     */
    public function setUser(?Authenticatable $user)
    {
        $this->user = $user;
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|string|null
     */
    public function id()
    {
        $user = $this->user();
        return $user->id ?? null;
    }

    public function viaRemember(): bool
    {
        return false;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     *
     * @throws BadMethodCallException
     *
     * @return bool
     */
    public function validate(array $credentials = []): bool
    {
        try {
            Keycloak::saveToken(Token::from($credentials));
            return $this->authenticate();
        } catch (Throwable $e) {
            Log::error($e);
            return false;
        }
    }

    /**
     * Try to authenticate the user
     */
    public function authenticate(): bool
    {
        // Get Credentials
        $credentials = Keycloak::retrieveToken();

        if (empty($credentials)) {
            return false;
        }

        $user = Keycloak::getUserInfo($credentials);

        if (empty($user)) {
            Keycloak::forgetToken();

            return false;
        }

        // Provide User
        $user = $this->provider->retrieveByCredentials($user->toArray());

        $this->setUser($user);

        return true;
    }

    /**
     * Check user is authenticated and return his resource roles
     *
     * @return array
     */
    public function roles(): array
    {
        if (! $this->check()) {
            return [];
        }

        $token = Keycloak::retrieveToken();

        if (blank($token)) {
            return [];
        }

        return $token->getAccessToken()->roles;
    }

    /**
     * Check user has a role
     */
    public function hasRole(string $role): bool
    {
        if (! $this->check()) {
            return false;
        }

        $token = Keycloak::retrieveToken();

        if (blank($token)) {
            return false;
        }

        return $token->getAccessToken()->hasRole($role);
    }
}
