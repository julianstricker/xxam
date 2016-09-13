<?php
namespace Xxam\UserBundle\Controller\Base;


/**
 * Trait UserTrait
 *
 * @author Julian Stricker <julianstricker@gmail.com>
 */
trait UserTrait
{
    private function getRoles(){
        $roles=Array();
        /*foreach ($this->container->getParameter('kernel.bundles') as $name ) {
            dump($name);
            if(method_exists($name,'getRoledefinitions')){

                $roles=array_merge($roles,call_user_func($name.'::getRoledefinitions'));
            }
        }*/
        foreach ($this->container->get('xxam_roles_holder') as $holder){
            $roles=array_merge($roles,$holder->getRoledefinitions());
        }
        return $roles;
    }
}