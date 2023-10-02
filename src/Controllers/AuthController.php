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
        $url = Keycloak::getRegistrationUrl();

        return redirect($url);
    }

    public function callback(Request $request): RedirectResponse
    {
        // Check for errors from Keycloak
        if (! empty($request->input('error'))) {
            $error = $request->input('error_description');
            $error = ($error) ?: $request->input('error');

            throw new RuntimeException($error);
        }

        // Check given state to mitigate CSRF attack
        $state = $request->input('state');
        if (empty($state) || ! Keycloak::validateState($state)) {
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
