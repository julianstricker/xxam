<?php

namespace Xxam\CoreBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * Contact
 *
 * @ORM\Table(name="extjsstate",
 *     indexes={
 *       @ORM\Index(name="ix_tenant_id", columns={"tenant_id"})
 *     })
 * @ORM\Entity(repositoryClass="Xxam\CoreBundle\Entity\ExtjsstateRepository")
 */
class Extjsstate implements Base\TenantInterface
{
    use Base\TenantTrait;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $user_id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="statekey", type="string", length=255, nullable=true)
     */
    private $statekey;
    
    /**
     * @var string
     *
     * @ORM\Column(name="statevalue", type="string", length=4000, nullable=true)
     */
    private $statevalue;

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
     * @ORM\ManyToOne(targetEntity="Xxam\UserBundle\Entity\User", inversedBy="extjsstates")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user_id
     *
     * @param integer $userId
     * @return Extjsstate
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;

        return $this;
    }

    /**
     * Get user_id
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set statekey
     *
     * @param string $statekey
     * @return Extjsstate
     */
    public function setStatekey($statekey)
    {
        $this->statekey = $statekey;

        return $this;
    }

    /**
     * Get statekey
     *
     * @return string 
     */
    public function getStatekey()
    {
        return $this->statekey;
    }

    /**
     * Set statevalue
     *
     * @param string $statevalue
     * @return Extjsstate
     */
    public function setStatevalue($statevalue)
    {
        $this->statevalue = $statevalue;

        return $this;
    }

    /**
     * Get statevalue
     *
     * @return string 
     */
    public function getStatevalue()
    {
        return $this->statevalue;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Extjsstate
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return Extjsstate
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set user
     *
     * @param \Xxam\UserBundle\Entity\User $user
     * @return Extjsstate
     */
    public function setUser(\Xxam\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Xxam\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
