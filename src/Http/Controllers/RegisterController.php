<?php

namespace Jkbennemann\Webauthn\Http\Controllers;

use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Jkbennemann\Webauthn\Enums\UserVerification;
use Jkbennemann\Webauthn\Exceptions\WebauthnException;
use Jkbennemann\Webauthn\Models\WebauthnKey;
use Jkbennemann\Webauthn\Service;

class RegisterController
{
    public function getOptions(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'display_name' => 'required|string',
        ]);

        $user = User::first();
        $userId = $user ? $user->id : 'testabcdefghijklmn';

        $webauthn = app(Service::class);
        try {
            $result = $webauthn->getCreateArgs(
                $userId,
                $validated['name'],
                $validated['display_name'],
                UserVerification::DISCOURAGED,
                null,
                true
            );

            ray(Cache::put(md5($userId.'_challenge'), [
                'challenge' => $result->challenge,
                'name' => $validated['name'],
                'display_name' => $validated['display_name'],
            ], 10));
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
        $validated = $request->validate([
            'clientDataJSON' => 'required|string',
            'attestationObject' => 'required|string',
        ]);

        $user = User::first();

        $userId = $user ? $user->id : 'testabcdefghijklmn';
        $challengeData = Cache::get(md5($userId.'_challenge'));

        ray($challengeData);

        $clientData = base64_decode($validated['clientDataJSON']);
        $attestationObject = base64_decode($validated['attestationObject']);

        $service = app(Service::class);
        $result = $service->processCreate(
            $clientData,
            $attestationObject,
            $challengeData['challenge'],
            false,
            false,
            false
        );

        $result->name = $challengeData['name'];
        $result->displayName = $challengeData['display_name'];

        ray($result);
        WebauthnKey::create([
            'user_id' => $userId,
            'credentialId' => $result->credentialId,
            'alias' => $result->displayName,
            'attestationFormat' => $result->attestationFormat,
            'credentialPublicKey' => $result->credentialPublicKey,
            'rootValid' => (bool) $result->rootValid,
            'userPresent' => $result->userPresent,
            'userVerified' => $result->userVerified,
            'aaguid' => $result->AAGUID,
        ]);

        return response()->json(null, 204);
    }
}
