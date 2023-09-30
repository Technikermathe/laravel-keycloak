<?php

declare(strict_types=1);

namespace Technikermathe\Keycloak\Data;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use stdClass;
use Technikermathe\Keycloak\Exceptions\InvalidAudienceException;
use Technikermathe\Keycloak\Exceptions\InvalidIssuerException;
use Technikermathe\Keycloak\Facades\Keycloak;

class Token extends Data
{
    public function __construct(
        public string $access_token,
        public int $expires_in,
        public int $refresh_expires_in,
        public string $refresh_token,
        public string $token_type,
        public string $id_token,
        #[MapInputName('not-before-policy')]
        public int $not_before_policy,
        public string $session_state,
        public string $scope,
    ) {
    }

    public function getAccessToken(): AccessToken
    {
        return AccessToken::from($this->decodeToken($this->access_token));
    }

    public function getIdToken(): IdToken
    {
        return IdToken::from($this->decodeToken($this->id_token));
    }

    public function getRefreshToken(): RefreshToken
    {
        return RefreshToken::from($this->decodeToken($this->refresh_token));
    }

    private function ensureIntegrity(stdClass $token): void
    {
        if ($token->iss !== Keycloak::getIssuer()) {
            throw new InvalidIssuerException();
        }
    }

    private function decodeToken(string $token): stdClass
    {
        $header = JWT::jsonDecode(JWT::urlsafeB64Decode(explode('.', $token)[0]));

        $object = JWT::decode($token, new Key(Keycloak::getPublicKey(), $header->alg));

        $this->ensureIntegrity($object);

        return $object;
    }
}
