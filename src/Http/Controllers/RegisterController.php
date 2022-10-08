<?php

namespace Jkbennemann\Webauthn\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Jkbennemann\Webauthn\Enums\UserVerification;
use Jkbennemann\Webauthn\Exceptions\WebauthnException;
use Jkbennemann\Webauthn\Service;

class RegisterController
{
    public function getOptions(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'display_name' => 'required|string',
        ]);

        $webauthn = app(Service::class);
        try {
            $result = $webauthn->getCreateArgs(
                Str::random(16),
                $validated['name'],
                $validated['display_name'],
                UserVerification::DISCOURAGED,
                false,
                true
            );
        } catch (WebauthnException $e) {
            return response()
                ->setStatusCode(500)
                ->json($e->getMessage());
        }

        return response()
            ->json($result);
    }

    public function store(Request $request)
    {
        return response()
            ->setStatusCode(204)
            ->json();
    }
}
