<?php
// src/Just/AdminBundle/Entity/User.php

namespace Just\AdminBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
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
     * @ORM\ManyToMany(targetEntity="Just\AdminBundle\Entity\Group")
     * @ORM\JoinTable(name="fos_user_user_group",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    protected $groups;
    
    
    /**
     * @ORM\ManyToMany(targetEntity="Just\MailclientBundle\Entity\Mailaccount", inversedBy="users")
     * @ORM\JoinTable(name="user_mailaccount")
     * */
    private $mailaccounts;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

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
     * Add mailaccounts
     *
     * @param \Just\MailclientBundle\Entity\Mailaccount $mailaccounts
     * @return User
     */
    public function addMailaccount(\Just\MailclientBundle\Entity\Mailaccount $mailaccounts)
    {
        $this->mailaccounts[] = $mailaccounts;

        return $this;
    }

    /**
     * Remove mailaccounts
     *
     * @param \Just\MailclientBundle\Entity\Mailaccount $mailaccounts
     */
    public function removeMailaccount(\Just\MailclientBundle\Entity\Mailaccount $mailaccounts)
    {
        $this->mailaccounts->removeElement($mailaccounts);
    }

    /**
     * Get mailaccounts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMailaccounts()
    {
        return $this->mailaccounts;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return User
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
     * @return User
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

}
