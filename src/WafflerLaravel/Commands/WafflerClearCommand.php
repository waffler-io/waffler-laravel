<?php

declare(strict_types=1);

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
use Symfony\Component\Console\Attribute\AsCommand;
use Waffler\Laravel\DirectoryResolvers\DirectoryResolverInterface;

#[AsCommand(
    'waffler:clear',
    'Clear cache directory.',
)]
class WafflerClearCommand extends Command
{
    public function __construct(private readonly DirectoryResolverInterface $directoryResolver)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $directory = $this->directoryResolver->resolve();
        if (is_dir($directory)) {
            array_map(unlink(...), glob("$directory/*.php"));
        }
        $this->info('Waffler Cache cleared.');
    }
}
