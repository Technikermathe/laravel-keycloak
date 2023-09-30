<?php

namespace Technikermathe\Keycloak\Data;

use Spatie\LaravelData\Data;

class IdToken extends Data
{
    public function __construct(
        public int $exp,
        public int $iat,
        public int $auth_time,
        public string $jti,
        public string $iss,
        public string $aud,
        public string $sub,
        public string $typ,
        public string $azp,
        public string $session_state,
        public string $at_hash,
        public string $acr,
        public string $sid,
        public bool $email_verified,
        public string $name,
        public string $preferred_username,
        public string $given_name,
        public string $family_name,
        public string $email
    ) {}
}
