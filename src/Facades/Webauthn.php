<?php

namespace Jkbennemann\Webauthn\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Jkbennemann\Webauthn\Service
 */
class Webauthn extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Jkbennemann\Webauthn\Service::class;
    }
}
