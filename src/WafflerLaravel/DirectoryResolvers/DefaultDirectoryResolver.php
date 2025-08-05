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

namespace Waffler\Laravel\DirectoryResolvers;

use Composer\InstalledVersions;
use Override;
use RuntimeException;

use function Illuminate\Filesystem\join_paths;

class DefaultDirectoryResolver implements DirectoryResolverInterface
{
    private ?string $cache = null;

    #[Override]
    public function resolve(): string
    {
        if ($this->cache) {
            return $this->cache;
        }
        $wafflerPath = InstalledVersions::getInstallPath('waffler/waffler');
        if (!$wafflerPath) {
            throw new RuntimeException('Waffler package path was not found.');
        }
        return $this->cache = join_paths($wafflerPath, 'generated');
    }
}
