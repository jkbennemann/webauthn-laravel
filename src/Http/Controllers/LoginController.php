<?php

namespace Jkbennemann\Webauthn\Http\Controllers;

use Illuminate\Http\Request;
use Jkbennemann\Webauthn\Enums\UserVerification;
use Jkbennemann\Webauthn\Exceptions\WebauthnException;
use Jkbennemann\Webauthn\Models\WebauthnKey;
use Jkbennemann\Webauthn\Service;

class LoginController
{
    public function getOptions(Request $request)
    {
        $userModel = config('webauthn.model');
        $user = $userModel::with('keys')->first();
        $keys = [];

        /** @var WebauthnKey $key */
        foreach ($user ? $user->keys : [] as $key) {
            $keys[] = $key->credentialId;
        }

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
