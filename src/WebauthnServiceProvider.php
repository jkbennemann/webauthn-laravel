<?php

namespace Jkbennemann\Webauthn;

use Jkbennemann\Webauthn\Commands\WebauthnCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class WebauthnServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('webauthn-laravel')
            ->hasConfigFile('webauthn')
            ->hasViews()
            ->hasMigration('create_webauthn-laravel_table')
            ->hasCommand(WebauthnCommand::class);
    }
}
