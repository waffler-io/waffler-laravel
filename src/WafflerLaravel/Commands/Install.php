<?php

/*
 * This file is part of Waffler.
 *
 * (c) Erick Johnson Almeida de Menezes <erickmenezes.dev@gmail.com>
 *
 * This source file is subject to the MIT licence that is bundled
 * with this source code in the file LICENCE.
 */

namespace Waffler\Laravel\Commands;

use Illuminate\Console\Command;

/**
 * Class Install.
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 */
class Install extends Command
{
    protected $signature = 'waffler:install';

    protected $description = 'Publishes the package assets.';

    public function handle(): bool
    {
        $this->callSilent('vendor:publish', [
            '--tag' => 'waffler-config'
        ]);

        $this->info('Installation completed.');

        return true;
    }
}
