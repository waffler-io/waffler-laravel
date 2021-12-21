<?php

/*
 * This file is part of Waffler.
 *
 * (c) Erick Johnson Almeida de Menezes <erickmenezes.dev@gmail.com>
 *
 * This source file is subject to the MIT licence that is bundled
 * with this source code in the file LICENCE.
 */

namespace Waffler\Laravel\Tests;

use Waffler\Laravel\WafflerServiceProvider;

/**
 * Class TestCase.
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 */
class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            WafflerServiceProvider::class,
        ];
    }
}
