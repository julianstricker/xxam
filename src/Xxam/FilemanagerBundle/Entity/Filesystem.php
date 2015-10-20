<?php

namespace Xxam\FilemanagerBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Xxam\CoreBundle\Entity\Base as Base;

/**
 * @ORM\Entity
 * @ORM\Table(name="filesystem",
 *     indexes={
 *       @ORM\Index(name="ix_tenant_id", columns={"tenant_id"})
 *     })
 * @ORM\Entity(repositoryClass="Xxam\FilemanagerBundle\Entity\FilesystemRepository")
 */
class Filesystem implements Base\TenantInterface
{
    use Base\TenantTrait;
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $user_id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="filesystemname", type="string", length=50)
     */
    protected $filesystemname;
    
    /**
     * @var string
     *
     * @ORM\Column(name="adapter", type="string", length=50)
     */
    protected $adapter;
    
    /**
     * @var string
     *
     * @ORM\Column(name="settings", type="string", length=1023)
     */
    protected $settings;
    
    
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
     * @ORM\ManyToOne(targetEntity="Xxam\UserBundle\Entity\User", inversedBy="filesystems")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;
    
    /**
     * @ORM\ManyToMany(targetEntity="Xxam\UserBundle\Entity\Group", mappedBy="filesystems")
     * @ORM\JoinTable(name="group_filesystem")
     * */
    private $groups;

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
     * Set user_id
     *
     * @param integer $userId
     * @return Filesystem
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
     * Set filesystemname
     *
     * @param string $filesystemname
     * @return Filesystem
     */
    public function setFilesystemname($filesystemname)
    {
        $this->filesystemname = $filesystemname;

        return $this;
    }

    /**
     * Get filesystemname
     *
     * @return string 
     */
    public function getFilesystemname()
    {
        return $this->filesystemname;
    }

    /**
     * Set adapter
     *
     * @param string $adapter
     * @return Filesystem
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * Get adapter
     *
     * @return string 
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Set settings
     *
     * @param string $settings
     * @return Filesystem
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * Get settings
     *
     * @return string 
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Filesystem
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
     * @return Filesystem
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
     * @return Filesystem
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

    /**
     * Add groups
     *
     * @param \Xxam\UserBundle\Entity\Group $groups
     * @return Filesystem
     */
    public function addGroup(\Xxam\UserBundle\Entity\Group $groups)
    {
        $this->groups[] = $groups;

        return $this;
    }

    /**
     * Remove groups
     *
     * @param \Xxam\UserBundle\Entity\Group $groups
     */
    public function removeGroup(\Xxam\UserBundle\Entity\Group $groups)
    {
        $this->groups->removeElement($groups);
    }

    /**
     * Get groups
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGroups()
    {
        return $this->groups;
    }
    
    public function toGridObject() {
        $groups=Array();
        if ($this->getGroups()){
            foreach($this->getGroups() as $group){
                $groups[]=$group->getName();
            }
        }
        return Array(
            'id' =>                     $this->getId(),
            'user_id' =>                $this->getUserId(),
            'user' =>                   $this->getUser()? $this->getUser()->getUsername() : null,
            'filesystemname' =>         $this->getFilesystemname(),
            'adapter' =>                $this->getAdapter(),
            'created' =>                $this->getCreated() ? $this->getCreated()->format('Y-m-d H:i:s') : false,
            'updated' =>                $this->getUpdated() ? $this->getUpdated()->format('Y-m-d H:i:s') : false,
            'groups' =>                 $groups
        );
    }
}