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
        bool $crossPlatform,
        bool $skipAttestation = false,
    ) {
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
}
