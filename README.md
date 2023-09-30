# Keycloak Guard for Laravel.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/technikermathe/laravel-keycloak.svg?style=flat-square)](https://packagist.org/packages/technikermathe/laravel-keycloak)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/technikermathe/laravel-keycloak/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/technikermathe/laravel-keycloak/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/technikermathe/laravel-keycloak/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/technikermathe/laravel-keycloak/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/technikermathe/laravel-keycloak.svg?style=flat-square)](https://packagist.org/packages/technikermathe/laravel-keycloak)

Very opinionated Keycloak Auth Guard implementation for Laravel.

## Key differences

- Meant for latest Keycloak and respective endpoints (22.x)
- Fixes various security flaws with other OIDC and JWT implementations
- JWT validation based on firebase/jwt
- Issuer and Audience Validation
- Data Normalization with DTOs

## Installation

You can install the package via composer:

```bash
composer require technikermathe/laravel-keycloak
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-keycloak-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-keycloak-config"
```

This is the contents of the published config file:

```php
return [
    'url' => env('KEYCLOAK_URL', 'https://keycloak.example.org'),
    'realm' => env('KEYCLOAK_REALM', 'keycloak'),
    'clientId' => env('KEYCLOAK_CLIENT_ID', 'https://app.example.org/auth/oidc'),
    'clientSecret' => env('KEYCLOAK_CLIENT_SECRET'),
    'model' => 'App\\Models\\User',
];
```

## Usage

```php
// Routes
route('login')
route('register')
route('callback')
route('logout')
```

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
