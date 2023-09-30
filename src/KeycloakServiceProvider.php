<?php

namespace Technikermathe\Keycloak;

use GuzzleHttp\Psr7\HttpFactory;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\UriFactoryInterface;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Technikermathe\Keycloak\Auth\Guard\KeycloakGuard;
use Technikermathe\Keycloak\Auth\KeycloakPersistentUserProvider;

class KeycloakServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-keycloak')
            ->hasConfigFile()
            ->hasRoute('web')
            ->hasMigration('create_users_table')
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations();
            });
    }

    public function packageBooted()
    {
        $this->registerBindings();
    }

    protected function registerBindings(): self
    {
        $this->app->bind(UriFactoryInterface::class, HttpFactory::class);
        $this->app->bind(Keycloak::class, function (Application $app) {
            $baseUrl = $app->get(UriFactoryInterface::class)
                ->createUri(config('keycloak.url').'/realms/'.config('keycloak.realm'));

            return new Keycloak(
                http: Http::baseUrl($baseUrl)->throw(),
                baseUrl: $baseUrl->__toString()
            );
        });

        Auth::provider('keycloak-persistent-users', function ($app, array $config) {
            return new KeycloakPersistentUserProvider($config['model']);
        });

        Auth::extend('keycloak', function ($app, $name, array $config) {
            $provider = Auth::createUserProvider($config['provider']);

            return new KeycloakGuard($provider, $app->request);
        });

        return $this;
    }
}
