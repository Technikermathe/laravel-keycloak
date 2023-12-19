<?php

namespace Technikermathe\Keycloak\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use RuntimeException;
use Technikermathe\Keycloak\Facades\Keycloak;

class AuthController extends Controller
{
    public function login(): RedirectResponse
    {
        Keycloak::saveState();

        return redirect(Keycloak::getAuthUrl());
    }

    public function logout(): RedirectResponse
    {
        $url = Keycloak::getLogoutUrl();

        Keycloak::forgetToken();
        Keycloak::forgetState();

        return redirect($url);
    }

    public function register(): RedirectResponse
    {
        Keycloak::saveState();

        $url = Keycloak::getRegistrationUrl();

        return redirect($url);
    }

    public function callback(Request $request): RedirectResponse
    {
        // Check for errors from Keycloak
        if ($request->has('error')) {
            $errorMessage = $request->string('error')->append($request->string('error_description', ''));

            throw new RuntimeException($errorMessage);
        }

        // Check given state to mitigate CSRF attack
        if (!$request->has('state') || ! Keycloak::validateState($request->string('state'))) {
            Keycloak::forgetState();

            throw new RuntimeException('Invalid state');
        }

        // Change code for token
        $token = Keycloak::getToken();

        if (Auth::validate($token->toArray())) {
            return redirect()->intended(config('keycloak.redirect_url', '/admin'));
        }

        throw new RuntimeException('Undefined State');
    }
}
