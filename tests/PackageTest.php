<?php

use Illuminate\Support\Facades\Route;

it('can test', function () {
    expect(true)->toBeTrue();
});

it('registers routes', function () {
    $routes = collect(Route::getRoutes())->map(function ($route) {
        return $route->getName();
    });

    $this->assertTrue($routes->contains('login'));
    $this->assertTrue($routes->contains('logout'));
    $this->assertTrue($routes->contains('register'));
    $this->assertTrue($routes->contains('callback'));
});

