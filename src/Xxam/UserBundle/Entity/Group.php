<?php

namespace Xxam\UserBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use FOS\UserBundle\Model\Group as BaseGroup;
use Doctrine\ORM\Mapping as ORM;
use Xxam\CoreBundle\Entity\Base as Base;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_group",
 *     indexes={
 *       @ORM\Index(name="ix_tenant_id", columns={"tenant_id"})
 *     })
 * @ORM\Entity(repositoryClass="Xxam\UserBundle\Entity\GroupRepository")
 */
class Group extends BaseGroup implements Base\TenantInterface
{
    use Base\TenantTrait;
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
     * @ORM\ManyToMany(targetEntity="Xxam\FilemanagerBundle\Entity\Filesystem", inversedBy="groups")
     * @ORM\JoinTable(name="group_filesystem")
     * */
    private $filesystems;

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
     * Set created
     *
     * @param \DateTime $created
     * @return Group
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
     * @return Group
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
    
    public function __toString(){
      return $this->getName();   
    }
    
    public function toGridObject() {
        
        return Array(
            'id' =>                     $this->getId(),
            'name' =>                   $this->getName(),
            'roles' =>                  $this->getRoles(),
            'created' =>                $this->getCreated() ? $this->getCreated()->format('Y-m-d H.i.s') : false,
            'updated' =>                $this->getUpdated() ? $this->getUpdated()->format('Y-m-d H.i.s') : false
        );
    }
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->filesystems = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * Add filesystems
     *
     * @param \Xxam\FilemanagerBundle\Entity\Filesystem $filesystems
     * @return Group
     */
    public function addFilesystem(\Xxam\FilemanagerBundle\Entity\Filesystem $filesystems)
    {
        $this->filesystems[] = $filesystems;

        return $this;
    }

    /**
     * Remove filesystems
     *
     * @param \Xxam\FilemanagerBundle\Entity\Filesystem $filesystems
     */
    public function removeFilesystem(\Xxam\FilemanagerBundle\Entity\Filesystem $filesystems)
    {
        $this->filesystems->removeElement($filesystems);
    }

    /**
     * Get filesystems
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFilesystems()
    {
        return $this->filesystems;
    }
}
