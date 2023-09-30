<?php

declare(strict_types=1);

namespace Technikermathe\Keycloak\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class Certs extends Data
{
    public function __construct(
        #[DataCollectionOf(Key::class)]
        public DataCollection $keys
    ) {
    }
}
