<?php

/*
 * This file is part of the Xxam package.
 *
 * (c) Julian Stricker <julian@julianstricker.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xxam\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class XxamCoreExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        //$configuration = new Configuration();

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('parameters.yml');
        if (!$container->hasDefinition('xxam_menuservices_holder')) {
            $taggedServiceHolder = new Definition();
            $taggedServiceHolder->setClass('SplDoublyLinkedList');
            $container->setDefinition('xxam_menuservices_holder', $taggedServiceHolder);
        }
        if (!$container->hasDefinition('xxam_portalwidget_holder')) {
            $taggedServiceHolder = new Definition();
            $taggedServiceHolder->setClass('SplDoublyLinkedList');
            $container->setDefinition('xxam_portalwidget_holder', $taggedServiceHolder);
        }
        if (!$container->hasDefinition('xxam_roles_holder')) {
            $taggedServiceHolder = new Definition();
            $taggedServiceHolder->setClass('SplDoublyLinkedList');
            $container->setDefinition('xxam_roles_holder', $taggedServiceHolder);
        }
    }

}
