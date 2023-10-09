<?php

return [
    'url' => env('KEYCLOAK_URL', 'https://keycloak.example.org'),
    'realm' => env('KEYCLOAK_REALM', 'keycloak'),
    'clientId' => env('KEYCLOAK_CLIENT_ID', 'https://app.example.org/auth/oidc'),
    'clientSecret' => env('KEYCLOAK_CLIENT_SECRET'),
    'model' => 'App\\Models\\User',
    'routeMiddleware' => 'web',
    'redirect_url' => '/kurse',
    'scope' => 'openid roles email profile',
];
