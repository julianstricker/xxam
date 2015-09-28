<?php
namespace Just\AdminBundle\Filter;
use Doctrine\ORM\Mapping\ClassMetaData;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Just\AdminBundle\Entity\Tenant;

class TenantFilter extends SQLFilter {

    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
      
        $filter=''; //$targetTableAlias . '.tenant_id IS NULL ';
        if ($targetEntity->reflClass->implementsInterface('Just\AdminBundle\Entity\Base\TenantInterface')){
            //$tenant_id=isset($_SESSION['tenant_id']) ? $_SESSION['tenant_id'] : 1; // $this->getParameter('tenant_id');
            $tenant_id= str_replace("'",'',$this->getParameter('tenant_id', 1));
            if ($tenant_id=="") $tenant_id=null;
            $filter = !is_null($tenant_id) ? $targetTableAlias . '.tenant_id = ' . $tenant_id. ' OR '.$targetTableAlias . '.tenant_id IS NULL' : '';
        }
        
        return $filter;
    }
}
