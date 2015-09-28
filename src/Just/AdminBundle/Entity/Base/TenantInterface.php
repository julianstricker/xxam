<?php

namespace Just\AdminBundle\Entity\Base;

interface TenantInterface {

    /**
     * Set tenant_id
     *
     * @param integer $tenantId
     */
    public function setTenantId($tenantId);

    /**
     * Get tenant_id
     *
     * @return integer 
     */
    public function getTenantId();
}
