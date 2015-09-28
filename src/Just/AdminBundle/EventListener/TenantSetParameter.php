<?php
namespace Just\AdminBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Just\AdminBundle\Entity\Hotel;
use Just\AdminBundle\Entity\LogUseraction;

class TenantSetParameter
{
    protected $container;
    protected $em;
    
    public function __construct(\Symfony\Component\DependencyInjection\Container $container,$em) {
        $this->container = $container;
        $this->em = $em;
    }
    
    protected function getSubdomain($request){
        $host = $request->getHost();
        $parts = explode('.', $host);
        $subdomain = 'admin';
        if (count($parts) === 3 ) {
            $subdomain = $parts[0];
        }
        return $subdomain;
    }
    protected function getTenantIdForSubdomain($subdomain){
        $tenants=$this->container->getParameter('tenants');
        foreach($tenants as $id=>$tenant){
            if ($tenant['subdomain']==$subdomain) return $id;
        }
        return 1;
    }
    
    public function onKernelRequest(\Symfony\Component\HttpKernel\Event\GetResponseEvent  $event)
    {
        $filter = $this->em->getFilters()->enable('tenant_filter');
        //if ($event->getRequest()->get('tenant_id',false)!=false) $tenant_id=$event->getRequest()->get('tenant_id',false);
        $subdomain = $this->getSubdomain($event->getRequest());
        $tenant_id=$this->getTenantIdForSubdomain($subdomain);
        $filter->setParameter('tenant_id', $tenant_id);
        $request = $event->getRequest();
        $request->getSession()->set('tenant_id', $tenant_id);
    }
    
    public function onCommand(\Symfony\Component\Console\Event\ConsoleCommandEvent $event)
    {
        $tenant_id=1;
        $filter = $this->em->getFilters()->enable('tenant_filter');
        $filter->setParameter('tenant_id', $tenant_id);
    }
        
    
}
?>
