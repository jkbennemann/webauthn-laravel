<?php

namespace Jkbennemann\Webauthn;

use Illuminate\Support\Facades\Route;
use Jkbennemann\Webauthn\Commands\WebauthnCommand;
use Jkbennemann\Webauthn\Http\Controllers\LoginController;
use Jkbennemann\Webauthn\Http\Controllers\RegisterController;
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
            ->hasMigration('create_webauthn_keys_table')
            ->hasCommand(WebauthnCommand::class);
    }

    public function packageBooted()
    {
        $this->app->bind(Configuration::class, function () {
            return new Configuration(
                config('webauthn.relying_party.name'),
                config('webauthn.relying_party.id'),
                config('webauthn.challenge.length', 32),
                config('webauthn.challenge.timeout', 5),
                $this->whitelistedOrigins(),
                //add configuration for custom transports
            );
        });

        $this->app->bind(Webauthn::class, function () {
            $config = $this->app->make(Configuration::class);

            return new Webauthn(
                $config,
                []
            );
        });
    }

    private function whitelistedOrigins(): array
    {
        if ($this->app->environment('production')) {
            return [];
        }

        return config('webauthn.whitelist', []);
    }

    public function packageRegistered()
    {
        if ($this->routesEnabled()) {
            Route::prefix($this->routePrefix())
                ->group(function () {
                    Route::get('login', [LoginController::class, 'getOptions'])->name('webauthn.login.get');
                    Route::post('login', [LoginController::class, 'login'])->name('webauthn.login.post');

                    Route::get('register', [RegisterController::class, 'getOptions'])->name('webauthn.register.get');
                    Route::post('register', [RegisterController::class, 'store'])->name('webauthn.register.post');
                });
        }
    }

    private function routePrefix(): string
    {
        $prefix = config('webauthn.routes.prefix', 'webauthn');

        if (! is_string($prefix)) {
            return '';
        }

        return $prefix;
    }

    private function routesEnabled(): bool
    {
        return (bool) config('webauthn.routes.enabled', true);
    }
}
