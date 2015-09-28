<?php

// src/Just/UserBundle/Entity/User.php

namespace Just\UserBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Just\AdminBundle\Entity\Base as Base;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user",
 *     indexes={
 *       @ORM\Index(name="ix_tenant_id", columns={"tenant_id"})
 *     })
 * @ORM\Entity(repositoryClass="Just\UserBundle\Entity\UserRepository")
 */
class User extends BaseUser implements Base\TenantInterface
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
     * @ORM\ManyToMany(targetEntity="Just\UserBundle\Entity\Group")
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

    /**
     * @ORM\OneToMany(targetEntity="Just\AdminBundle\Entity\Widget", mappedBy="user", cascade={"persist", "remove"})
     * @ORM\OrderBy({"sortfield" = "ASC"})
     */
    private $widgets;
    
    
    
    /**
     * @ORM\OneToMany(targetEntity="Just\FilemanagerBundle\Entity\Filesystem", mappedBy="user", cascade={"persist", "remove"})
     */
    private $filesystems;

    /**
     * @ORM\OneToMany(targetEntity="Just\AdminBundle\Entity\Extjsstate", mappedBy="user", cascade={"persist", "remove"})
     */
    private $extjsstates;

    public function __construct() {
        parent::__construct();
        // your own logic
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Add mailaccounts
     *
     * @param \Just\MailclientBundle\Entity\Mailaccount $mailaccounts
     * @return User
     */
    public function addMailaccount(\Just\MailclientBundle\Entity\Mailaccount $mailaccounts) {
        $this->mailaccounts[] = $mailaccounts;

        return $this;
    }

    /**
     * Remove mailaccounts
     *
     * @param \Just\MailclientBundle\Entity\Mailaccount $mailaccounts
     */
    public function removeMailaccount(\Just\MailclientBundle\Entity\Mailaccount $mailaccounts) {
        $this->mailaccounts->removeElement($mailaccounts);
    }

    /**
     * Get mailaccounts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMailaccounts() {
        return $this->mailaccounts;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return User
     */
    public function setCreated($created) {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return User
     */
    public function setUpdated($updated) {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated() {
        return $this->updated;
    }

    /**
     * Add widgets
     *
     * @param Just\AdminBundle\Entity\Widget $widgets
     */
    public function addWidget(\Just\AdminBundle\Entity\Widget $widgets) {
        $this->widgets[] = $widgets;
    }

    /**
     * Get widgets
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getWidgets() {
        return $this->widgets;
    }

    /**
     * Add extjsstates
     *
     * @param Just\AdminBundle\Entity\Extjsstate $extjsstates
     */
    public function addExtjsstate(\Just\AdminBundle\Entity\Extjsstate $extjsstates) {
        $this->extjsstates[] = $extjsstates;
    }

    /**
     * Get extjsstates
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getExtjsstates() {
        return $this->extjsstates;
    }
    
    public function getDirectroles(){
        return $this->roles;
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
            'username' =>               $this->getUsername(),
            'email' =>                  $this->getEmail(),
            'last_login' =>             $this->getLastLogin() ? $this->getLastLogin()->format('Y-m-d H:i:s') : false,
            'locked' =>                 $this->isLocked(),
            'expired' =>                $this->isExpired(),
            'expires_at' =>             $this->expiresAt ? $this->expiresAt->format('Y-m-d H:i:s') : false,
            'credentials_expired' =>    $this->isCredentialsExpired(),
            'credentials_expire_at' =>  $this->credentialsExpireAt ? $this->credentialsExpireAt->format('Y-m-d H:i:s') : false,
            'created' =>                $this->getCreated() ? $this->getCreated()->format('Y-m-d H:i:s') : false,
            'updated' =>                $this->getUpdated() ? $this->getUpdated()->format('Y-m-d H:i:s') : false,
            'groups' =>                 $groups,
            'roles' =>                  $this->getDirectroles(),
        );
    }

    

    /**
     * Remove widgets
     *
     * @param \Just\AdminBundle\Entity\Widget $widgets
     */
    public function removeWidget(\Just\AdminBundle\Entity\Widget $widgets)
    {
        $this->widgets->removeElement($widgets);
    }

    /**
     * Add filesystems
     *
     * @param \Just\FilemanagerBundle\Entity\Filesystem $filesystems
     * @return User
     */
    public function addFilesystem(\Just\FilemanagerBundle\Entity\Filesystem $filesystems)
    {
        $this->filesystems[] = $filesystems;

        return $this;
    }

    /**
     * Remove filesystems
     *
     * @param \Just\FilemanagerBundle\Entity\Filesystem $filesystems
     */
    public function removeFilesystem(\Just\FilemanagerBundle\Entity\Filesystem $filesystems)
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

    /**
     * Remove extjsstates
     *
     * @param \Just\AdminBundle\Entity\Extjsstate $extjsstates
     */
    public function removeExtjsstate(\Just\AdminBundle\Entity\Extjsstate $extjsstates)
    {
        $this->extjsstates->removeElement($extjsstates);
    }
}
