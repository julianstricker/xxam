<?php

namespace Xxam\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class AggregatedTaggedServicesPass implements CompilerPassInterface
{
    /**
     * @see Symfony\Component\DependencyInjection\Compiler.CompilerPassInterface::process()
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('xxam_menuservices_holder') || !$container->has('xxam_portalwidget_holder') || !$container->has('xxam_roles_holder')) {
            return;
        }
        $taggedServiceHolder = $container->findDefinition('xxam_menuservices_holder');
        foreach ($container->findTaggedServiceIds('xxam.menuservice') as $id => $attributes) {
            $taggedServiceHolder->addMethodCall('push', array(new Reference($id)));
        }
        $taggedServiceHolder = $container->findDefinition('xxam_portalwidget_holder');
        foreach ($container->findTaggedServiceIds('xxam.portalwidget') as $id => $attributes) {
            $taggedServiceHolder->addMethodCall('push', array(new Reference($id)));
        }
        $taggedServiceHolder = $container->findDefinition('xxam_roles_holder');
        foreach ($container->findTaggedServiceIds('xxam.rolesservice') as $id => $attributes) {
            $taggedServiceHolder->addMethodCall('push', array(new Reference($id)));
        }
    }
}