<?php

/*
 * This file is part of the Xxam package.
 *
 * (c) Julian Stricker <julian@julianstricker.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xxam\CoreBundle\Entity;

use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * LogEntry
 *
 * @ORM\Table(name="logentries",
 *     indexes={
 *       @ORM\Index(name="ix_tenant_id", columns={"tenant_id"}),
 *       @ORM\Index(name="log_class_lookup_idx", columns={"object_class"}),
 *       @ORM\Index(name="log_date_lookup_idx", columns={"logged_at"}),
 *       @ORM\Index(name="log_user_lookup_idx", columns={"username"}),
 *       @ORM\Index(name="log_version_lookup_idx", columns={"object_id", "object_class", "version"})
 *     })
 * @ORM\Entity(repositoryClass="LogEntryRepository")
 */
class LogEntry extends AbstractLogEntry implements Base\TenantInterface
{
    use Base\TenantTrait;

}
