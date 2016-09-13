<?php
namespace Xxam\DynmodBundle\Services;

use Doctrine\ORM\EntityManager;
use Xxam\DynmodBundle\Entity\Dynmod;

class RolesService
{
    protected $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function getRoledefinitions()
    {
        $roles= [
            "ROLE_DYNMOD_LIST", "ROLE_DYNMOD_CREATE", "ROLE_DYNMOD_EDIT", "ROLE_DYNMOD_DELETE",
            "ROLE_DYNMOD_DATACONTAINER_LIST", "ROLE_DYNMOD_DATACONTAINER_CREATE", "ROLE_DYNMOD_DATACONTAINER_EDIT", "ROLE_DYNMOD_DATACONTAINER_DELETE"
        ];
        $repository=$this->em->getRepository('Xxam\DynmodBundle\Entity\Dynmod');
        /** @var Dynmod $dynmod */
        foreach($repository->findAll() as $dynmod){
            $dynmodroles=$dynmod->getAdditionalroles();
            $roles[]="ROLE_DYNMOD_".strtoupper($dynmod->getCode()).'_LIST';
            $roles[]="ROLE_DYNMOD_".strtoupper($dynmod->getCode()).'_CREATE';
            $roles[]="ROLE_DYNMOD_".strtoupper($dynmod->getCode()).'_EDIT';
            $roles[]="ROLE_DYNMOD_".strtoupper($dynmod->getCode()).'_DELETE';
            foreach($dynmodroles as $dynmodrole){
                $roles[]="ROLE_DYNMOD_".strtoupper($dynmod->getCode()).'_'.$dynmodrole;
            }
        }
        //dump($roles);
        return $roles;
    }
}