<?php

namespace Just\CalendarBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="mailaccount")
 */
class Mailaccount
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="accounttype", type="string", length=50)
     */
    protected $accounttype;
    
    /**
     * @var string
     *
     * @ORM\Column(name="connectionstring", type="string", length=255)
     */
    protected $connectionstring;
    
    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255)
     */
    protected $username;
    
    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    protected $password;
    
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
     * @ORM\ManyToMany(targetEntity="Just\AdminBundle\Entity\User", mappedBy="mailaccounts")
     * @ORM\JoinTable(name="user_mailaccount")
     * */
    private $users;

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
     * Set accounttype
     *
     * @param string $accounttype
     * @return Mailaccount
     */
    public function setAccounttype($accounttype)
    {
        $this->accounttype = $accounttype;

        return $this;
    }

    /**
     * Get accounttype
     *
     * @return string 
     */
    public function getAccounttype()
    {
        return $this->accounttype;
    }

    /**
     * Set connectionstring
     *
     * @param string $connectionstring
     * @return Mailaccount
     */
    public function setConnectionstring($connectionstring)
    {
        $this->connectionstring = $connectionstring;

        return $this;
    }

    /**
     * Get connectionstring
     *
     * @return string 
     */
    public function getConnectionstring()
    {
        return $this->connectionstring;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return Mailaccount
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return Mailaccount
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Add users
     *
     * @param \Just\AdminBundle\Entity\User $users
     * @return Mailaccount
     */
    public function addUser(\Just\AdminBundle\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \Just\AdminBundle\Entity\User $users
     */
    public function removeUser(\Just\AdminBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }
}
