<?php

namespace Technikermathe\Keycloak\Casts;

use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Support\DataProperty;
use Throwable;

class UriCast implements Cast
{
    public function cast(DataProperty $property, mixed $value, array $properties, array $context): UriInterface|Uncastable
    {
        if (! is_string($value)) {
            return Uncastable::create();
        }

        try {
            return app(UriFactoryInterface::class)->createUri($value);
        } catch (Throwable) {
            return Uncastable::create();
        }
    }
}
