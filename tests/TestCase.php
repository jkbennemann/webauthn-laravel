<?php

namespace Jkbennemann\Webauthn\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jkbennemann\Webauthn\WebauthnServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Jkbennemann\\Service\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            WebauthnServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        $migration = include __DIR__.'/../database/migrations/create_webauthn_keys_table.php.stub';
        $migration->up();
    }
}
