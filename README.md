# Keycloak Guard for Laravel.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/technikermathe/laravel-keycloak.svg?style=flat-square)](https://packagist.org/packages/technikermathe/laravel-keycloak)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/technikermathe/laravel-keycloak/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/technikermathe/laravel-keycloak/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/technikermathe/laravel-keycloak/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/technikermathe/laravel-keycloak/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/technikermathe/laravel-keycloak.svg?style=flat-square)](https://packagist.org/packages/technikermathe/laravel-keycloak)

Very opinionated Keycloak Auth Guard implementation for Laravel meant to replace any other authentication providers.

You can use this when you really *only* want to use Keycloak in a traditional Laravel Application. This is a straightforward
way to add SSO to filament, for example.

## !!! ⚠️ This is still considered experimental ⚠️ !!!

While we do use it in production, we only use it as a complete replacement for any other auth solution.

## Features

- Provides an Auth driver for Keycloak in Laravel
- Various Keycloak helpers, see the `src/Keycloak.php`

## Key differences

- Meant for latest Keycloak and respective endpoints (>=22.x)
- Fixes various security flaws with other OIDC and JWT implementations
- Battle proven JWT validation based on firebase/jwt
- Issuer* and Audience Validation

## Installation

You can install the package via composer:

```bash
composer require technikermathe/laravel-keycloak
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="keycloak-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="keycloak-config"
```

This is the contents of the published config file:

```php
<?php

return [
    'url' => env('KEYCLOAK_URL', 'https://keycloak.example.org'),
    'realm' => env('KEYCLOAK_REALM', 'keycloak'),
    'clientId' => env('KEYCLOAK_CLIENT_ID', 'https://app.example.org/auth/oidc'),
    'clientSecret' => env('KEYCLOAK_CLIENT_SECRET'),
    'model' => 'App\\Models\\User',
    'routeMiddleware' => 'web',
    'redirect_url' => '/',
    'scope' => 'openid roles email profile',
];

```

## Usage

### Add auth driver in `config/auth.php`

The default `keycloak-persistent-users` driver persists and updates user data from the ID Token to the local database.

Feel free to supply your own user provider here.

```php
'guards' => [
    'web' => [
        'driver' => 'keycloak',
        'provider' => 'users',
    ],
],

'providers' => [
    'users' => [
        'driver' => 'keycloak-persistent-users',
        'model' => App\Models\User::class,
    ],
]
```

```php
// Routes
route('login')
route('register')
route('logout')

// Middleware
middleware(['auth']) # Require authentication
middleware(['can:admin']) # Check for resource role "admin"
```

### Example: Usage with filament panel

In this example, we want to make the filament panel only accessible for admin users.


```php
// app/Providers/AdminPanelProvider
// ...
public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->authGuard('web')
        ->middleware(['auth', 'can:admin'])
        ->login(null)
        // ...
}
// ...

```

### Example: Usage with laravel/pulse, laravel/horizon....

```php
// config/pulse.php
// config/horizon.php
return [
    // ...
    'middleware' => ['web', 'auth', 'can:admin'],
    // ...
]
```

## Alternatives

- Use https://github.com/robsontenorio/laravel-keycloak-guard if you need to secure an API
- Use https://github.com/mariovalney/laravel-keycloak-web-guard for older Keycloak versions

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Pascale Beier](https://github.com/Technikermathe)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
