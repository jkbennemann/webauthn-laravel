<?php

namespace Jkbennemann\Webauthn\Http\Controllers;

use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Jkbennemann\Webauthn\Enums\UserVerification;
use Jkbennemann\Webauthn\Exceptions\WebauthnException;
use Jkbennemann\Webauthn\Service;

class LoginController
{
    public function getOptions(Request $request)
    {
        $user = User::first();
        $keys = $user ? $user->keys : [];

        $webauthn = app(Service::class);
        try {
            $result = $webauthn->getVerificationArgs(
                UserVerification::DISCOURAGED,
                $keys
            );

            ray($result);
        } catch (WebauthnException $e) {
            return response()
                ->setStatusCode(500)
                ->json($e->getMessage());
        }

        return response()
            ->json($result);
    }

    public function login(Request $request)
    {
    }
}
