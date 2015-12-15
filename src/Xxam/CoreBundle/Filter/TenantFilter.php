<?php

/*
 * This file is part of the Xxam package.
 *
 * (c) Julian Stricker <julian@julianstricker.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xxam\CoreBundle\Filter;
use Doctrine\ORM\Mapping\ClassMetaData;
use Doctrine\ORM\Query\Filter\SQLFilter;

class TenantFilter extends SQLFilter {

    /**
     * addFilterConstraint
     *
     * @param ClassMetaData $targetEntity
     * @param string $targetTableAlias
     * @return string
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
      
        $filter=''; //$targetTableAlias . '.tenant_id IS NULL ';
        if ($targetEntity->reflClass->implementsInterface('Xxam\CoreBundle\Entity\Base\TenantInterface')){
            //$tenant_id=isset($_SESSION['tenant_id']) ? $_SESSION['tenant_id'] : 1; // $this->getParameter('tenant_id');
            $tenant_id= str_replace("'",'',$this->getParameter('tenant_id'));
            if ($tenant_id=="") $tenant_id=null;
            $filter = !is_null($tenant_id) ? $targetTableAlias . '.tenant_id = ' . $tenant_id. ' OR '.$targetTableAlias . '.tenant_id IS NULL' : '';
        }
        
        return $filter;
    }
}
