<?php

/*
 * This file is part of the Xxam package.
 *
 * (c) Julian Stricker <julian@julianstricker.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xxam\CoreBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Xxam\CoreBundle\DependencyInjection\Compiler\AggregatedTaggedServicesPass;

class XxamCoreBundle extends Bundle
{
    /**
     * @see Symfony\Component\HttpKernel\Bundle.Bundle::registerExtensions()
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new AggregatedTaggedServicesPass());
    }


}
