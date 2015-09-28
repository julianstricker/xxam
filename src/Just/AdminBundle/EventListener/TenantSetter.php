<?php
namespace Just\AdminBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Just\AdminBundle\Entity\Base\TenantInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class TenantSetter
{
     

    public function prePersist(LifecycleEventArgs $args)
    {
        
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();
        
        $filter = $entityManager->getFilters()->enable('tenant_filter');
        $tenant_id=str_replace("'",'',$filter->getParameter('tenant_id', 1));
        //echo ($tenant_id);
        
        if ($entity instanceof TenantInterface){
            $entity->setTenantId($tenant_id);
            
        }
    }
}
?>
