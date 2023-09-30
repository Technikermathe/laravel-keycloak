<?php

declare(strict_types=1);

namespace Technikermathe\Keycloak\Data;

use Spatie\LaravelData\Data;

class UserInfo extends Data
{
    public function __construct(
        public string $sub,
        public bool $email_verified,
        public array $realm_access,
        public string $name,
        public string $preferred_username,
        public string $given_name,
        public string $family_name,
        public string $email,
    ) {
    }
}
