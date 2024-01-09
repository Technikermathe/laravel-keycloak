<?php

namespace Technikermathe\Keycloak\Auth\Guard;

use BadMethodCallException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Technikermathe\Keycloak\Data\Token;
use Technikermathe\Keycloak\Facades\Keycloak;
use Throwable;

class KeycloakGuard implements Guard
{
    /**
     * @var null|Authenticatable
     */
    protected $user;

    /**
     * Constructor.
     */
    public function __construct(protected UserProvider $provider, protected Request $request)
    {
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
     *
     * @throws BadMethodCallException
     */
    public function validate(array $credentials = []): bool
    {
        try {
            Keycloak::saveToken(Token::from($credentials));

            return $this->authenticate();
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }

    /**
     * Try to authenticate the user
     */
    public function authenticate(): bool
    {
        /** @var Token $credentials */
        $credentials = Keycloak::retrieveToken();

        if (! $credentials instanceof Token) {
            return false;
        }

        $credentials = Keycloak::refreshTokenIfNeeded($credentials);

        $idToken = $credentials->getIdToken();

        // Provide User
        $user = $this->provider->retrieveByCredentials($idToken->toArray());

        $this->setUser($user);

        return true;
    }

    /**
     * Check user is authenticated and return his resource roles
     */
    public function roles(): array
    {
        if (! $this->check()) {
            return [];
        }

        $token = Keycloak::retrieveToken();

        if ($token === null) {
            return [];
        }

        $token = Keycloak::refreshTokenIfNeeded($token);

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

        if ($token === null) {
            return false;
        }

        $token = Keycloak::refreshTokenIfNeeded($token);

        return $token->getAccessToken()->hasRole($role);
    }

    public function logout(): never
    {
        abort(to_route('logout'));
    }
}
