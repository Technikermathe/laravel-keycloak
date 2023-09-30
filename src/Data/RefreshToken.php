<?php

namespace Technikermathe\Keycloak\Data;

use Spatie\LaravelData\Data;

class RefreshToken extends Data
{
    public function __construct(
        public int $exp,
        public int $iat,
        public string $jti,
        public string $iss,
        public string $aud,
        public string $sub,
        public string $typ,
        public string $azp,
        public string $session_state,
        public string $scope,
        public string $sid
    ) {
    }
}
