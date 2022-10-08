<?php

namespace Jkbennemann\Webauthn\Commands;

use Illuminate\Console\Command;

class WebauthnCommand extends Command
{
    public $signature = 'webauthn-laravel';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
