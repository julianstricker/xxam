<?php
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