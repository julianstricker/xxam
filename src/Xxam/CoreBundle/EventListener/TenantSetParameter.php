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

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;


class TenantSetParameter
{
    protected $container;
    protected $em;

    /**
     * TenantSetParameter constructor.
     * @param Container $container
     * @param $em
     */
    public function __construct(Container $container, EntityManager $em) {
        $this->container = $container;
        $this->em = $em;
    }

    /**
     * Returns Subdomain String for Request
     * @param Request $request
     * @return string
     */
    protected function getSubdomain(Request $request){
        $host = $request->getHost();
        $parts = explode('.', $host);
        $subdomain = 'admin';
        if (count($parts) === 3 ) {
            $subdomain = $parts[0];
        }
        return $subdomain;
    }

    /**
     * Returns Tenant Id for Subdomain
     * @param $subdomain
     * @return int|string
     */
    protected function getTenantIdForSubdomain($subdomain){
        $tenants=$this->container->getParameter('tenants');
        foreach($tenants as $id=>$tenant){
            if ($tenant['subdomain']==$subdomain) return $id;
        }
        return 1;
    }

    /**
     * onKernelRequest Event Handler
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent  $event)
    {
        $filter = $this->em->getFilters()->enable('tenant_filter');
        //if ($event->getRequest()->get('tenant_id',false)!=false) $tenant_id=$event->getRequest()->get('tenant_id',false);
        $subdomain = $this->getSubdomain($event->getRequest());
        $tenant_id=$this->getTenantIdForSubdomain($subdomain);
        $filter->setParameter('tenant_id', $tenant_id);
        $request = $event->getRequest();
        $request->getSession()->set('tenant_id', $tenant_id);
    }

    /**
     * onCommand Event Handler
     */
    public function onCommand()
    {
        $tenant_id=1;
        $filter = $this->em->getFilters()->enable('tenant_filter');
        $filter->setParameter('tenant_id', $tenant_id);
    }
        
    
}
?>
