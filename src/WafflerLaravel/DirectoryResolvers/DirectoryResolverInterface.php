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

interface DirectoryResolverInterface
{
    /**
     * @return non-empty-string
     * @author ErickJMenezes <erickmenezes.dev@gmail.com>
     */
    public function resolve(): string;
}
