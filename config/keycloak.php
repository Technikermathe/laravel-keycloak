<?php

return [
    'url' => env('KEYCLOAK_URL', 'https://login.technikermathe.de'),
    'realm' => env('KEYCLOAK_REALM', 'technikermathe'),
    'clientId' => env('KEYCLOAK_CLIENT_ID', 'https://technikermathe.test/auth/oidc'),
    'clientSecret' => env('KEYCLOAK_CLIENT_SECRET'),
    'model' => 'App\\Models\\User',
];
