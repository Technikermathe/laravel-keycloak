<?php

namespace Technikermathe\Keycloak;

use Firebase\JWT\ExpiredException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Technikermathe\Keycloak\Data\Certs;
use Technikermathe\Keycloak\Data\OpenIdConfiguration;
use Technikermathe\Keycloak\Data\Token;
use Technikermathe\Keycloak\Data\UserInfo;
use Throwable;

class Keycloak
{
    private const ENDPOINT_OPENID_CONFIGURATION = '/.well-known/openid-configuration';

    private const SESSION = 'keycloak_session';

    private const STATE = 'keycloak_state';

    private string $state;

    public function __construct(
        private readonly PendingRequest $http,
        private readonly string $baseUrl,
    ) {
        $this->state = Str::random(64);
    }

    public function getIssuer(): string
    {
        return $this->baseUrl;
    }

    public function getOpenIdConfiguration(): OpenIdConfiguration
    {
        return Cache::rememberForever('openidconfiguration', fn () => OpenIdConfiguration::from(
            $this->http->get(self::ENDPOINT_OPENID_CONFIGURATION)->json()
        ));
    }

    public function getCertificates(): Certs
    {
        return Cache::rememberForever('certs', function () {
            $openIdConfiguration = $this->getOpenIdConfiguration();

            return Certs::from(
                $this->http->get((string) $openIdConfiguration->jwks_uri)->json()
            );
        });
    }

    public function getPublicKey(): string
    {
        return Cache::rememberForever('public_key', function () {
            $openIdConfiguration = $this->getOpenIdConfiguration();

            $key = $this->http->get((string) $openIdConfiguration->issuer)->json('public_key');

            if (! is_string($key)) {
                return '';
            }

            return "-----BEGIN PUBLIC KEY-----\n".wordwrap($key, 64, "\n", true)."\n-----END PUBLIC KEY-----";
        });
    }

    public function getAuthUrl(string $redirectUri = ''): string
    {
        $redirectUri = $redirectUri === '' ? route('callback') : $redirectUri;

        $openIdConfiguration = $this->getOpenIdConfiguration();

        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => config('keycloak.clientId'),
            'redirect_uri' => $redirectUri,
            'scope' => config('keycloak.scope'),
            'state' => $this->state,
        ]);

        return $openIdConfiguration
            ->authorization_endpoint
            ->withQuery($query)
            ->__toString();
    }

    public function getLogoutUrl(string $postLogoutRedirectUri = ''): string
    {
        $postLogoutRedirectUri = $postLogoutRedirectUri === '' ? url('/') : $postLogoutRedirectUri;

        $openIdConfiguration = $this->getOpenIdConfiguration();

        $query = http_build_query([
            'client_id' => config('keycloak.clientId'),
            'state' => $this->state,
            'post_logout_redirect_uri' => $postLogoutRedirectUri,
        ]);

        return $openIdConfiguration
            ->end_session_endpoint
            ->withQuery($query)
            ->__toString();
    }

    public function getRegistrationUrl(string $redirectUri = ''): string
    {
        $redirectUri = $redirectUri === '' ? route('callback') : $redirectUri;

        $openIdConfiguration = $this->getOpenIdConfiguration();

        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => config('keycloak.clientId'),
            'redirect_uri' => $redirectUri,
            'scope' => config('keycloak.scope'),
            'state' => $this->state,
        ]);

        $authUrl = $openIdConfiguration
            ->authorization_endpoint
            ->withQuery($query)
            ->__toString();

        return Str::replaceLast(
            '/auth',
            '/registrations',
            $authUrl
        );
    }

    public function getToken(): Token
    {
        $validated = request()->validate([
            'code' => 'required',
        ]);

        $openIdConfiguration = $this->getOpenIdConfiguration();

        $endpoint = (string) $openIdConfiguration->token_endpoint;
        $body = [
            'grant_type' => 'authorization_code',
            'client_id' => config('keycloak.clientId'),
            'client_secret' => config('keycloak.clientSecret'),
            'redirect_uri' => route('callback'),
            'code' => $validated['code'],
            'state' => $this->state,
        ];

        $response = $this->http->asForm()->post($endpoint, $body);

        return Token::from($response->json());
    }

    public function refreshAccessToken(Token $token): Token
    {
        $openIdConfiguration = $this->getOpenIdConfiguration();

        $endpoint = (string) $openIdConfiguration->token_endpoint;
        $body = [
            'grant_type' => 'refresh_token',
            'client_id' => config('keycloak.clientId'),
            'client_secret' => config('keycloak.clientSecret'),
            'redirect_uri' => route('callback'),
            'refresh_token' => $token->refresh_token,
        ];

        $response = $this->http->asForm()->post($endpoint, $body);

        return Token::from($response->json());
    }

    public function getUserInfo(Token $token): UserInfo
    {
        $token = $this->refreshTokenIfNeeded($token);

        $accessToken = $token->access_token;

        $user = $this->getOpenIdConfiguration()->userinfo_endpoint->__toString();

        $response = $this->http->withToken($accessToken)->get($user);

        return UserInfo::from($response->json());
    }

    public function refreshTokenIfNeeded(Token $token): Token
    {
        try {
            $token->getAccessToken();
        } catch (ExpiredException) {
            return $this->handleExpiredToken($token);
        } catch (Throwable) {
            $this->handleInvalidToken();
        }

        return $token;
    }

    private function handleExpiredToken(Token $token): Token
    {
        try {
            $token = $this->refreshAccessToken($token);
            $this->saveToken($token);
        } catch (Throwable) {
            $this->handleThrowable();
        }

        return $token;
    }

    private function handleInvalidToken(): never
    {
        $this->handleThrowable();
    }

    private function handleThrowable(): never
    {
        $this->forgetToken();
        $this->forgetState();
        abort(to_route('login'));
    }

    /**
     * Retrieve Token from Session
     */
    public function retrieveToken(): ?Token
    {
        /** @phpstan-ignore-next-line */
        return Session::get(self::SESSION);
    }

    /**
     * Save Token to Session
     */
    public function saveToken(Token $token): void
    {
        Session::put(self::SESSION, $token);
        Session::save();
    }

    /**
     * Remove Token from Session
     */
    public function forgetToken(): void
    {
        Session::forget(self::SESSION);
        Session::save();
    }

    /**
     * Validate State from Session
     */
    public function validateState(string $state): bool
    {
        $challenge = Session::get(self::STATE);

        return $challenge !== null && $state === $challenge;
    }

    /**
     * Save State to Session
     */
    public function saveState(): void
    {
        Session::put(self::STATE, $this->state);
        Session::save();
    }

    /**
     * Remove State from Session
     */
    public function forgetState(): void
    {
        Session::forget(self::STATE);
        Session::save();
    }
}
