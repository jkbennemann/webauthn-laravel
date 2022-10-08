<?php

namespace Jkbennemann\Webauthn;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Jkbennemann\Webauthn\Commands\WebauthnCommand;

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
