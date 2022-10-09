<?php

namespace Jkbennemann\Webauthn;

use Jkbennemann\Webauthn\Exceptions\WebauthnException;

class Service
{
    public function __construct(private Webauthn $webauthn)
    {
    }

    /**
     * @throws WebauthnException
     */
    public function getCreateArgs(
        string $userIdentifier,
               $name,
               $displayName,
               $verificationType,
        ?bool $crossPlatform,
        bool $skipAttestation = false,
    ): PublicKey {
        return $this->webauthn->getCreateArgs(
            $userIdentifier,
            $name,
            $displayName,
            $verificationType,
            $crossPlatform,
            [],
            $skipAttestation
        );
    }

    /**
     * @throws WebauthnException
     */
    public function getVerificationArgs(
        $verificationType,
        array $existingCredentialIds = [],
    ): PublicKey {
        return $this->webauthn->getVerifyArgs(
            $verificationType,
            $existingCredentialIds
        );
    }

    public function processCreate(
        string $clientData,
        string $attestationObject,
               $challenge,
               $requireUserVerification = false,
               $requireUserPresent = true,
               $failIfRootMismatch = true
    ) {
        return $this->webauthn->processCreate(
            $clientData,
            $attestationObject,
            $challenge,
            $requireUserVerification,
            $requireUserPresent,
            $failIfRootMismatch
        );
    }

    /**
     * @throws WebauthnException
     */
    public function processVerify(
        string $clientData,
        string $attestationObject,
        string $signature,
        string $credentialPublicKey,
        string $challenge,
        ?int $prevSignatureCnt = null,
        bool $requireUserVerification = false,
        bool $requireUserPresent = true,
    ): bool {
        return $this->webauthn->processGet(
            $clientData,
            $attestationObject,
            $signature,
            $credentialPublicKey,
            $challenge,
            $prevSignatureCnt,
            $requireUserVerification,
            $requireUserPresent
        );
    }
}
