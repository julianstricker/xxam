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
