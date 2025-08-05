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

namespace Unit\Commands;

use Override;
use PHPUnit\Framework\Attributes\Test;
use Waffler\Laravel\DirectoryResolvers\DirectoryResolverInterface;
use Waffler\Laravel\Tests\Fixtures\Interfaces\FooClientInterface;
use Waffler\Laravel\Tests\TestCase;

class WafflerOptimizeCacheTest extends TestCase
{
    #[Override]
    protected function getPackageProviders($app): array
    {
        $app['config']->set('waffler.clients', [
            FooClientInterface::class => ['base_uri' => 'localhost']
        ]);
        return parent::getPackageProviders($app);
    }

    #[Test]
    public function it_must_pre_generate_all_interfaces_inside_vendor_folder(): void
    {
        $directoryResolver = app(DirectoryResolverInterface::class);
        $this->artisan('waffler:clear')->assertSuccessful();
        $vendorPath = $directoryResolver->resolve();
        $result = glob("{$vendorPath}/*FooClientInterface*.php");
        $this->assertCount(0, $result);
        $this->artisan('waffler:cache')->assertSuccessful();
        $result = glob("{$vendorPath}/*FooClientInterface*.php");
        $this->assertCount(1, $result);
    }
}
