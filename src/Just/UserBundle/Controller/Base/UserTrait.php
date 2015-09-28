<?php
namespace Just\UserBundle\Controller\Base;


/**
 * Trait UserTrait
 *
 * @author Julian Stricker <julianstricker@gmail.com>
 */
trait UserTrait
{
    private function getRoles(){
        $roles=Array();
        foreach ($this->container->getParameter('kernel.bundles') as $name ) {
            if(method_exists($name,'getRoledefinitions')){
                $roles=array_merge($roles,call_user_func($name.'::getRoledefinitions'));
            }
        }
        return $roles;
    }
}