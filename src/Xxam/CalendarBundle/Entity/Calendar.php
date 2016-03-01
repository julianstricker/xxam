<?php

namespace Xxam\CalendarBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Xxam\CoreBundle\Entity\Base as Base;

/**
 * Calendar
 *
 * @ORM\Table(name="calendar",
 *     indexes={
 *       @ORM\Index(name="ix_tenant_id", columns={"tenant_id"})
 *     })
 * @ORM\Entity(repositoryClass="Xxam\CalendarBundle\Entity\CalendarRepository")
 */
class Calendar implements Base\TenantInterface
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
     * @ORM\Column(name="calendar_id", type="integer", nullable=true)
     */
    private $calendar_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="calendartype_id", type="integer", nullable=true)
     */
    private $calendartype_id;

    /**
     * @var string
     *
     * @ORM\Column(name="organizationname", type="string", length=255, nullable=true)
     */
    private $organizationname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255, nullable=true)
     */
    private $lastname;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255, nullable=true)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="nameprefix", type="string", length=255, nullable=true)
     */
    private $nameprefix;

    /**
     * @var string
     *
     * @ORM\Column(name="initials", type="string", length=255, nullable=true)
     */
    private $initials;

    /**
     * @var string
     *
     * @ORM\Column(name="nickname", type="string", length=255, nullable=true)
     */
    private $nickname;

    /**
     * @var string
     *
     * @ORM\Column(name="vat", type="string", length=255, nullable=true)
     */
    private $vat;

    /**
     * @var string
     *
     * @ORM\Column(name="tax", type="string", length=255, nullable=true)
     */
    private $tax;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birthday", type="date", nullable=true)
     */
    private $birthday;

    /**
     * @var string
     *
     * @ORM\Column(name="photo", type="string", length=255, nullable=true)
     */
    private $photo;

    /**
     * @var string
     *
     * @ORM\Column(name="organizationfunction", type="string", length=255, nullable=true)
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set calendar_id
     *
     * @param integer $calendarId
     * @return Calendar
     */
    public function setCalendarId($calendarId)
    {
        $this->calendar_id = $calendarId;

        return $this;
    }

    /**
     * Get calendar_id
     *
     * @return integer 
     */
    public function getCalendarId()
    {
        return $this->calendar_id;
    }

    /**
     * Set calendartype_id
     *
     * @param integer $calendartypeId
     * @return Calendar
     */
    public function setCalendartypeId($calendartypeId)
    {
        $this->calendartype_id = $calendartypeId;

        return $this;
    }

    /**
     * Get calendartype_id
     *
     * @return integer 
     */
    public function getCalendartypeId()
    {
        return $this->calendartype_id;
    }

    /**
     * Set organizationname
     *
     * @param string $organizationname
     * @return Calendar
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
     * @return Calendar
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
     * @return Calendar
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
     * @return Calendar
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
     * @return Calendar
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
     * @return Calendar
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
     * @return Calendar
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
     * @return Calendar
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
     * @return Calendar
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
     * @return Calendar
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
     * @return Calendar
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
     * @return Calendar
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
     * @return Calendar
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
        $this->addresses = new ArrayCollection();
    }



    
}
