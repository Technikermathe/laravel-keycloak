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

            $unformatted = $this->http->get((string) $openIdConfiguration->issuer)->json('public_key');
            $wrappedPublicKey = trim(chunk_split($unformatted, 64));

            return <<<EOD
            -----BEGIN PUBLIC KEY-----
            $wrappedPublicKey
            -----END PUBLIC KEY-----
            EOD;
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
            'scope' => 'openid roles email profile',
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
            'scope' => 'openid roles email profile',
            'state' => $this->state,
        ]);

        $authUrl = $openIdConfiguration
            ->authorization_endpoint
            ->withQuery($query)
            ->__toString();

        return Str::replaceEnd(
            'openid-connect/auth',
            'openid-connect/registrations',
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
            'redirect_uri' => route('keycloak.callback'),
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
            'redirect_uri' => route('keycloak.callback'),
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

            return $token;
        } catch (ExpiredException) {
            try {
                $token->getRefreshToken();

                return $this->refreshAccessToken($token);

            } catch (ExpiredException) {
                $this->forgetToken();
                $this->forgetState();
                abort(to_route('login'));
            }
        }
    }

    /**
     * Retrieve Token from Session
     */
    public function retrieveToken(): ?Token
    {
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
        $this->state = Str::random(64);
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
