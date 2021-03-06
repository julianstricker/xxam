<?php

namespace Xxam\MailclientBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Xxam\CoreBundle\Entity\Base as Base;

/**
 * @ORM\Entity
 * @ORM\Table(name="mailaccountuser",
 *     indexes={
 *       @ORM\Index(name="ix_tenant_id", columns={"tenant_id"})
 *     })
 * @ORM\Entity(repositoryClass="Xxam\MailclientBundle\Entity\MailaccountuserRepository")
 * @Gedmo\Loggable(logEntryClass="Xxam\CoreBundle\Entity\LogEntry")
 */
class Mailaccountuser implements Base\TenantInterface
{
    use Base\TenantTrait;
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     *
     * @ORM\Column(name="user_id", type="integer")
     * @Gedmo\Versioned
     */
    protected $user_id;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;

    
    /**
     * @ORM\ManyToOne(targetEntity="Xxam\MailclientBundle\Entity\Mailaccount", inversedBy="mailaccountusers")
     * @ORM\JoinColumn(name="mailaccount_id", referencedColumnName="id")
     * */
    private $mailaccount;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param string $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param \DateTime $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }

    /**
     * @return Mailaccount
     */
    public function getMailaccount()
    {
        return $this->mailaccount;
    }

    /**
     * @param Mailaccount $mailaccount
     */
    public function setMailaccount($mailaccount)
    {
        $this->mailaccount = $mailaccount;
    }




}
