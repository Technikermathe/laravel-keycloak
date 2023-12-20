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
    'publicKeyAlgorithm' => \Technikermathe\Keycloak\Keycloak::DEFAULT_ALGO,
    'jwt' => [
        'leeway' => \Technikermathe\Keycloak\Data\Token::LEEWAY,
    ]
];
