<?php

namespace Xxam\ContactBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Xxam\CoreBundle\Entity\Base as Base;

/**
 * Contact
 *
 * @ORM\Table(name="contact",
 *     indexes={
 *       @ORM\Index(name="ix_tenant_id", columns={"tenant_id"})
 *     })
 * @ORM\Entity(repositoryClass="Xxam\ContactBundle\Entity\ContactRepository")
 * @Gedmo\Loggable(logEntryClass="Xxam\CoreBundle\Entity\LogEntry")
 */
class Contact implements Base\TenantInterface
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
     * @var integer
     *
     * @ORM\Column(name="contact_id", type="integer", nullable=true)
     * @Gedmo\Versioned
     */
    private $contact_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="contacttype_id", type="integer", nullable=true)
     * @Gedmo\Versioned
     */
    private $contacttype_id;

    /**
     * @var string
     *
     * @ORM\Column(name="organizationname", type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $organizationname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $lastname;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="nameprefix", type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $nameprefix;

    /**
     * @var string
     *
     * @ORM\Column(name="initials", type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $initials;

    /**
     * @var string
     *
     * @ORM\Column(name="nickname", type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $nickname;

    /**
     * @var string
     *
     * @ORM\Column(name="vat", type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $vat;

    /**
     * @var string
     *
     * @ORM\Column(name="tax", type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $tax;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birthday", type="date", nullable=true)
     * @Gedmo\Versioned
     */
    private $birthday;

    /**
     * @var string
     *
     * @ORM\Column(name="photo", type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $photo;

    /**
     * @var string
     *
     * @ORM\Column(name="organizationfunction", type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $organizationfunction;
    
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
     * @ORM\OneToMany(targetEntity="Address", mappedBy="contact", cascade={"persist", "remove"})
     */
    private $addresses;
    
    /**
     * @ORM\OneToMany(targetEntity="Communicationdata", mappedBy="contact", cascade={"persist", "remove"})
     */
    private $communicationdatas;

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
     * Set contact_id
     *
     * @param integer $contactId
     * @return Contact
     */
    public function setContactId($contactId)
    {
        $this->contact_id = $contactId;

        return $this;
    }

    /**
     * Get contact_id
     *
     * @return integer 
     */
    public function getContactId()
    {
        return $this->contact_id;
    }

    /**
     * Set contacttype_id
     *
     * @param integer $contacttypeId
     * @return Contact
     */
    public function setContacttypeId($contacttypeId)
    {
        $this->contacttype_id = $contacttypeId;

        return $this;
    }

    /**
     * Get contacttype_id
     *
     * @return integer 
     */
    public function getContacttypeId()
    {
        return $this->contacttype_id;
    }

    /**
     * Set organizationname
     *
     * @param string $organizationname
     * @return Contact
     */
    public function setOrganizationname($organizationname)
    {
        $this->organizationname = $organizationname;

        return $this;
    }

    /**
     * Get organizationname
     *
     * @return string 
     */
    public function getOrganizationname()
    {
        return $this->organizationname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return Contact
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string 
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     * @return Contact
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string 
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set nameprefix
     *
     * @param string $nameprefix
     * @return Contact
     */
    public function setNameprefix($nameprefix)
    {
        $this->nameprefix = $nameprefix;

        return $this;
    }

    /**
     * Get nameprefix
     *
     * @return string 
     */
    public function getNameprefix()
    {
        return $this->nameprefix;
    }

    /**
     * Set initials
     *
     * @param string $initials
     * @return Contact
     */
    public function setInitials($initials)
    {
        $this->initials = $initials;

        return $this;
    }

    /**
     * Get initials
     *
     * @return string 
     */
    public function getInitials()
    {
        return $this->initials;
    }

    /**
     * Set nickname
     *
     * @param string $nickname
     * @return Contact
     */
    public function setNickname($nickname)
    {
        $this->nickname = $nickname;

        return $this;
    }

    /**
     * Get nickname
     *
     * @return string 
     */
    public function getNickname()
    {
        return $this->nickname;
    }

    /**
     * Set vat
     *
     * @param string $vat
     * @return Contact
     */
    public function setVat($vat)
    {
        $this->vat = $vat;

        return $this;
    }

    /**
     * Get vat
     *
     * @return string 
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * Set tax
     *
     * @param string $tax
     * @return Contact
     */
    public function setTax($tax)
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * Get tax
     *
     * @return string 
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * Set birthday
     *
     * @param \DateTime $birthday
     * @return Contact
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Get birthday
     *
     * @return \DateTime 
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set photo
     *
     * @param string $photo
     * @return Contact
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * Get photo
     *
     * @return string 
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Set organizationfunction
     *
     * @param string $organizationfunction
     * @return Contact
     */
    public function setOrganizationfunction($organizationfunction)
    {
        $this->organizationfunction = $organizationfunction;

        return $this;
    }

    /**
     * Get organizationfunction
     *
     * @return string 
     */
    public function getOrganizationfunction()
    {
        return $this->organizationfunction;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Contact
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
     * @return Contact
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
     * Constructor
     */
    public function __construct()
    {
        $this->addresses = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add addresses
     *
     * @param \Xxam\ContactBundle\Entity\Address $addresses
     * @return Contact
     */
    public function addAddress(\Xxam\ContactBundle\Entity\Address $addresses)
    {
        $this->addresses[] = $addresses;

        return $this;
    }

    /**
     * Remove addresses
     *
     * @param \Xxam\ContactBundle\Entity\Address $addresses
     */
    public function removeAddress(\Xxam\ContactBundle\Entity\Address $addresses)
    {
        $this->addresses->removeElement($addresses);
    }

    /**
     * Get addresses
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * Add communicationdatas
     *
     * @param \Xxam\ContactBundle\Entity\Communicationdata $communicationdatas
     * @return Contact
     */
    public function addCommunicationdata(\Xxam\ContactBundle\Entity\Communicationdata $communicationdatas)
    {
        $this->communicationdatas[] = $communicationdatas;

        return $this;
    }

    /**
     * Remove communicationdatas
     *
     * @param \Xxam\ContactBundle\Entity\Communicationdata $communicationdatas
     */
    public function removeCommunicationdata(\Xxam\ContactBundle\Entity\Communicationdata $communicationdatas)
    {
        $this->communicationdatas->removeElement($communicationdatas);
    }

    /**
     * Get communicationdatas
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCommunicationdatas()
    {
        return $this->communicationdatas;
    }
    
    
}
