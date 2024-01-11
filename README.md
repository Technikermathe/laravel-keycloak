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
- Automatically creates and updates a User Model to the database with the `keycloak-persistent-users` driver
- Various Keycloak helpers, see the `src/Keycloak.php`
- Tested with keycloak 22.x and 23.x

## Key differences

- Meant for latest Keycloak and respective endpoints (>=22.x)
- Fixes various security flaws with other OIDC and JWT implementations
- Battle proven JWT validation based on firebase/jwt
- Issuer* and Audience Validation
- ACL is read from the Access Token
- UserInfo is read from the ID Token

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
    'routeMiddleware' => 'web',
    'redirect_url' => '/',
    'scope' => 'openid roles email profile',
    'publicKeyAlgorithm' => \Technikermathe\Keycloak\Keycloak::DEFAULT_ALGO,
    'jwt' => [
        'leeway' => \Technikermathe\Keycloak\Data\Token::LEEWAY,
    ]
];

```

## Usage

### 1. Add auth driver in `config/auth.php`

The default `keycloak-persistent-users` driver persists and updates user data from the ID Token to the local database.

If you need a different approach or do not want to store Users in the database at all, feel free to supply your own user provider here.

```php
'guards' => [
    'web' => [
        'driver' => 'keycloak',
        'provider' => 'users',
    ],
],

'providers' => [
    'users' => [
        // See KeycloakPersistentUserProvider for how to implement your user provider.
        // This provider just creates or updates users from Keycloak.
        // See the example user migration to be used here.
        'driver' => 'keycloak-persistent-users',
        'model' => App\Models\User::class,
    ],
]
```

### 2. Adapt User Model

See the published User migration for a User model that goes well with a standard Keycloak setup.

The most important for the default driver that comes with this package, `User`s do not have a password field, which has some implications below.

In most cases you will only have to remove any `password` references from the User model.

Below is an example in conjunction with filament.
```php
<?php

namespace App\Models;


// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    public $incrementing = false;

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return Auth::hasRole('admin');
    }
}
```

### 3. Remove AuthenticateSession Middleware

Ensure that you are not using the `AuthenticateSession` Middleware in either first- or third-party packages as it 
expects your User Model to have a password attribute.


### 4. Use routes in your application
```php
// Routes
route('login')
route('register')
route('logout')

// Middleware
middleware(['auth']) # Require authentication
middleware(['can:admin']) # Check for resource role "admin"
```

### 5. Enable caching

Ensure you are using a cache. The openidconfiguration endpoints and public keys are cached.

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
