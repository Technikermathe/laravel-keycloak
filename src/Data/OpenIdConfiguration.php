<?php

declare(strict_types=1);

namespace Technikermathe\Keycloak\Data;

use Psr\Http\Message\UriInterface;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Technikermathe\Keycloak\Casts\UriCast;

class OpenIdConfiguration extends Data
{
    public function __construct(
        #[WithCast(UriCast::class)]
        public UriInterface $issuer,
        #[WithCast(UriCast::class)]
        public UriInterface $authorization_endpoint,
        #[WithCast(UriCast::class)]
        public UriInterface $token_endpoint,
        #[WithCast(UriCast::class)]
        public UriInterface $introspection_endpoint,
        #[WithCast(UriCast::class)]
        public UriInterface $userinfo_endpoint,
        #[WithCast(UriCast::class)]
        public UriInterface $end_session_endpoint,
        #[WithCast(UriCast::class)]
        public UriInterface $jwks_uri,
        #[WithCast(UriCast::class)]
        public UriInterface $check_session_iframe,
        #[WithCast(UriCast::class)]
        public UriInterface $registration_endpoint,
        public bool $frontchannel_logout_session_supported,
        public bool $frontchannel_logout_supported,
        public bool $backchannel_logout_session_supported,
        public bool $backchannel_logout_supported,
    ) {
    }

}
