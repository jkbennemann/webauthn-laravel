<?php

namespace Jkbennemann\Webauthn\Http\Controllers;

use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Jkbennemann\Webauthn\Enums\UserVerification;
use Jkbennemann\Webauthn\Exceptions\WebauthnException;
use Jkbennemann\Webauthn\Models\WebauthnKey;
use Jkbennemann\Webauthn\Service;

class LoginController
{
    public function getOptions(Request $request)
    {

        /** @var User $user */
        $userModel = config('webauthn.model');
        $user = $userModel::with('keys')::get()->first();
        $userId = $user ? $user->getAuthIdentifier() : '1';
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

            Cache::put(md5($userId.'_login_challenge'), [
                'challenge' => $result->challenge,
                'user_verification' => $result->authenticatorSelection->userVerification,
            ], 10);

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
        $validated = $request->validate([
            'clientDataJSON' => 'required|string',
            'attestationObject' => 'required|string',
            'signature' => 'required|string',
            'userHandle' => 'required|string',
            'id' => 'required|string',
        ]);

        $userModel = config('webauthn.model');
        /** @var User $user */
        $user = $userModel::with('keys')::get()->first();
        $userId = $user ? $user->getAuthIdentifier() : '1';

        $challengeData = Cache::get(md5($userId.'_login_challenge'));
        $clientData = base64_decode($validated['clientDataJSON']);
        $attestationObject = base64_decode($validated['attestationObject']);
        $signature = base64_decode($validated['signature']);
        $userHandle = base64_decode($validated['userHandle']);
        $credentialId = base64_decode($validated['id']);
        $challenge = $challengeData['challenge'];
        $userVerification = $challengeData['user_verification'];
        $credentialPublicKey = null;

        //find key where credentialId matches $credentialId
        //get associated user and publicKey

        $credentialId = bin2hex($credentialId);
        $userHandle = bin2hex($userHandle);

        ray("data", $credentialId, $userHandle);

        $service = app(Service::class);

        try {
            $service->processVerify(
                $clientData, $attestationObject, $signature, $credentialPublicKey, $challenge, null, $userVerification === 'required'
            );

            //log user in

            return response()->json();
        } catch (WebauthnException $e) {
            return response()
                ->setStatusCode(500)
                ->json($e->getMessage());
        }

    }
}
