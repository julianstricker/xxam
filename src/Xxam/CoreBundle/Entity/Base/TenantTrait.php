<?php

/*
 * This file is part of the Xxam package.
 *
 * (c) Julian Stricker <julian@julianstricker.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xxam\CoreBundle\Entity\Base;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * Trait TenantTrait
 *
 * @author Julian Stricker <julianstricker@gmail.com>
 */
trait TenantTrait
{
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $tenant_id;
    
    /**
     * Set tenant_id
     *
     * @param integer $tenantId
     */
    public function setTenantId($tenantId)
    {
        $this->tenant_id = $tenantId;
    }

    /**
     * Get tenant_id
     *
     * @return integer 
     */
    public function getTenantId()
    {
        return $this->tenant_id;
    }
}