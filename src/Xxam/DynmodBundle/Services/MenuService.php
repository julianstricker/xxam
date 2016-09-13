<?php
namespace Xxam\DynmodBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Xxam\DynmodBundle\Entity\Dynmod;

class MenuService
{
    /** @var EntityManager $em */
    protected $em;

    /** @var  TokenStorage $securityTokenStorage */
    protected $securityTokenStorage;

    public function __construct(TokenStorage $securityTokenStorage, EntityManager $entityManager)
    {
        $this->securityTokenStorage = $securityTokenStorage;
        $this->em = $entityManager;

    }
    public function getMenu() {
        $repository=$this->em->getRepository('XxamDynmodBundle:Dynmod');
        /** @var Dynmod[] $dynmods */
        $dynmods=$repository->findBy(['active'=>true]);
        $menu=Array(
            'dynmodmenu' => Array(
                'text' => 'Dynmod',
                'iconCls' => 'x-fa fa-cubes',
                #'handler'=>    'function() { window.location.href="#portal"}',
                'stateId' => 'xxam_menu_dynmod',
                'role' => 'ROLE_DYNMOD_LIST',
                'menu' => Array(
                    'menu_dynmod' => Array(
                        'text' => 'Dynmod',
                        'iconCls' => 'x-fa fa-cubes',
                        'href' => '#dynmod',
                        'role' => 'ROLE_DYNMOD_LIST'
                    ),
                    'menu_dynmod_create' => Array(
                        'text' => 'Create new Dynmod',
                        'iconCls' => 'x-fa fa-cube',
                        'href' => '#dynmod',
                        'role' => 'ROLE_DYNMOD_CREATE'
                    ),
                    'menu_dynmod_datacontainer' => Array(
                        'text' => 'Datacontainer',
                        'iconCls' => 'x-fa fa-cubes',
                        'href' => '#dynmod/datacontainer',
                        'role' => 'ROLE_DYNMOD_DATACONTAINER_LIST'
                    ),
                    'menu_dynmod_datacontainer-create' => Array(
                        'text' => 'Create new Dynmod',
                        'iconCls' => 'x-fa fa-cube',
                        'href' => '#dynmod/datacontainer/create',
                        'role' => 'ROLE_DYNMOD_DATACONTAINER_CREATE'
                    )
                )
            )
        );

        foreach($dynmods as $dynmod){
            $menu[$dynmod->getCode().'menu']=[
                'text' => $dynmod->getName(),
                'iconCls' => $dynmod->getIconcls(),
                'stateId' => 'xxam_menu_dynmod_'.$dynmod->getCode(),
                'role' => 'ROLE_DYNMOD_'.strtoupper($dynmod->getCode()).'_LIST',
                'menu' => Array(
                    'menu_dynmod' => Array(
                        'text' => $dynmod->getName(),
                        'iconCls' => $dynmod->getIconcls(),
                        'href' => '#dynmod/index/'.$dynmod->getCode(),
                        'role' => 'ROLE_DYNMOD_'.strtoupper($dynmod->getCode()).'_LIST'
                    )
                )

            ];
        }
        return $menu;
    }
}