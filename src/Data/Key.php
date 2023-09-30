<?php

declare(strict_types=1);

namespace Technikermathe\Keycloak\Data;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class Key extends Data
{
    public function __construct(
        public string $kid,
        public string $kty,
        public string $alg,
        public string $use,
        public string $n,
        public string $e,
        public array $x5c,
        public string $x5t,
        #[MapInputName('x5t#S256')]
        public string $x5tS256,
    ) {
    }
}
