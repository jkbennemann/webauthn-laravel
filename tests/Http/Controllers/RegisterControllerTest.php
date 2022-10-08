<?php

use Illuminate\Testing\TestResponse;

it('generates options to create a new key', function () {
    /** @var TestResponse $response */
    $response = $this->getJson(route('webauthn.register').'?'.http_build_query([
        'name' => 'Test',
        'display_name' => 'Test Display',
    ]));

    $response->assertOk();
});
