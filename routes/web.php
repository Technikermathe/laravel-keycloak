<?php

use Illuminate\Support\Facades\Route;

Route::middleware(config('keycloak.routeMiddleware', 'web'))->group(function () {
    Route::get('/login', 'Technikermathe\Keycloak\Controllers\AuthController@login')->name('login');
    Route::get('/logout', 'Technikermathe\Keycloak\Controllers\AuthController@logout')->name('logout');
    Route::get('/register', 'Technikermathe\Keycloak\Controllers\AuthController@register')->name('register');
    Route::get('/callback', 'Technikermathe\Keycloak\Controllers\AuthController@callback')->name('callback');
});
