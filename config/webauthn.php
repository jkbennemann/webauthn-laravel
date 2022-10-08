<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Relaying Party
    |--------------------------------------------------------------------------
    |
    | We will use your application information to inform the device who is the
    | relaying party. While only the name is enough, you can further set the
    | a custom domain as ID and even an icon image data encoded as BASE64.
    |
    */

    'relying_party' => [
        'name' => env('WEBAUTHN_NAME', config('app.name')),
        'id' => env('WEBAUTHN_ID', 'localhost'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Challenge configuration
    |--------------------------------------------------------------------------
    |
    | When making challenges your application needs to push at least 16 bytes
    | of randomness. Since we need to later check them, we'll also store the
    | bytes for a small amount of time inside this current request session.
    |
    */

    'challenge' => [
        'length' => 32,
        'timeout' => 60,
        'key' => '_webauthn',
    ],

    'whitelist' => [
        'localhost',
        //add custom domains for testing
    ],

    'model' => App\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | Routes configuration
    |--------------------------------------------------------------------------
    |
    | You may customize the handling of the routes.
    | Out of the box all relevant routes will be provided to you.
    | If you want to create a custom logic yourself you man disable the routes
    | here or define a different prefix that suits your needs
    |
    */
    'routes' => [
        'enabled' => true,
        'prefix' => 'webauthn',
    ],
];
