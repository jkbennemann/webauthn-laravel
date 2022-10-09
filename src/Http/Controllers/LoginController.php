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
        $user = $userModel::query()->with('keys')->first();
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
                'user_verification' => null,
            ], 60 * 3);

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
        $user = $userModel::query()->with('keys')->first();
        $userId = $user ? $user->getAuthIdentifier() : '1';

        $challengeData = Cache::get(md5($userId.'_login_challenge'));
        Cache::forget(md5($userId.'_login_challenge'));

        $clientData = base64_decode($validated['clientDataJSON']);
        $attestationObject = base64_decode($validated['attestationObject']);
        $signature = base64_decode($validated['signature']);
        $userHandle = base64_decode($validated['userHandle']);
        $credentialId = base64_decode($validated['id']);
        $challenge = $challengeData['challenge']->getBinaryString();
        $userVerification = $challengeData['user_verification'];
        $credentialId = bin2hex($credentialId);

        $service = app(Service::class);

        try {
            //find key where credentialId matches $credentialId
            //get associated user and publicKey
            /** @var WebauthnKey $key */
            $key = WebauthnKey::with('user')->where('credentialId', $credentialId)->first();

            if (! $key || ! $key->user->getKey() == (int) $userHandle) {
                throw new WebauthnException('could not verify your key');
            }

            $service->processVerify(
                $clientData, $attestationObject, $signature, $key->credentialPublicKey, $challenge, $key->signatureCounter, $userVerification === 'required'
            );

            //log user in
            $key->trackLogin();

            return response()->json([
                'key' => $key->alias,
                'last_login' => $key->lastLogin->getTimestamp(),
            ]);
        } catch (WebauthnException $e) {
            ray($e);

            return response()
                ->json($e->getMessage(), 500);
        }
    }
}
