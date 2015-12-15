<?php

/*
 * This file is part of the Xxam package.
 *
 * (c) Julian Stricker <julian@julianstricker.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xxam\CoreBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Xxam\CoreBundle\Entity\Base\TenantInterface;

class TenantSetter
{
    /**
     * prePersist Event Handler
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();
        $filter = $entityManager->getFilters()->enable('tenant_filter');
        $tenant_id = str_replace("'", '', $filter->getParameter('tenant_id'));
        if ($entity instanceof TenantInterface) {
            $entity->setTenantId($tenant_id);
        }
    }
}

?>
