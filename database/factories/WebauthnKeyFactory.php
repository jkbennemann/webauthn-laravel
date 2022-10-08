<?php

namespace Jkbennemann\Webauthn\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jkbennemann\Webauthn\Models\WebauthnKey;

class WebauthnKeyFactory extends Factory
{
    protected $model = WebauthnKey::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->slug,
        ];
    }
}
