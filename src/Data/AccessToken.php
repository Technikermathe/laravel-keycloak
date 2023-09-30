<?php

namespace Technikermathe\Keycloak\Data;

use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class AccessToken extends Data
{
    #[Computed]
    public array $roles;

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
        public string $acr,
        #[MapInputName('allowed-origins')]
        public array $allowed_origins,
        public array $realm_access,
        public array $resource_access,
        public string $scope,
        public string $sid,
        public bool $email_verified,
        public string $name,
        public string $preferred_username,
        public string $given_name,
        public string $family_name,
        public string $email
    ) {
        $this->roles = $this->realm_access['roles'] ?? [];
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }
}
