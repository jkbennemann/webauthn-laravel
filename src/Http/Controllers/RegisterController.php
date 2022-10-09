<?php

namespace Jkbennemann\Webauthn\Http\Controllers;

use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
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
            //'name' => 'required|string',
            'display_name' => 'required|string',
        ]);

        /** @var User $user */
        $userModel = config('webauthn.model');
        $user = $userModel::query()->with('keys')->first();
        $userId = $user ? $user->getAuthIdentifier() : '1';

        $webauthn = app(Service::class);
        try {
            $result = $webauthn->getCreateArgs(
                $userId,
                $user->name,
                $validated['display_name'],
                UserVerification::DISCOURAGED,
                null,
                true
            );
            Cache::put(md5($userId.'_challenge'), [
                'challenge' => $result->challenge,
                'name' => $validated['name'],
                'user_verification' => $result->authenticatorSelection->userVerification,
                'display_name' => $validated['display_name'],
            ], 60 * 3);
        } catch (WebauthnException $e) {
            return response()
                ->json($e->getMessage(), 500);
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

        /** @var User $user */
        $userModel = config('webauthn.model');
        $user = $userModel::query()->with('keys')->first();
        $userId = $user ? $user->getAuthIdentifier() : '1';

        $challengeData = Cache::get(md5($userId.'_challenge'));
        Cache::forget(md5($userId.'_challenge'));

        $clientData = base64_decode($validated['clientDataJSON']);
        $attestationObject = base64_decode($validated['attestationObject']);

        $service = app(Service::class);
        $result = $service->processCreate(
            $clientData,
            $attestationObject,
            $challengeData['challenge']->getBinaryString(),
            $challengeData['user_verification'] === UserVerification::REQUIRED,
            true,
            false
        );

        $result->name = $challengeData['name'];
        $result->displayName = $challengeData['display_name'];

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
