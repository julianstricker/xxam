<?php

namespace Just\AdminBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * Contact
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Just\AdminBundle\Entity\ContactRepository")
 */
class Contact
{
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
     * @ORM\Column(name="contact_id", type="integer")
     */
    private $contact_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="contacttype_id", type="integer")
     */
    private $contacttype_id;

    /**
     * @var string
     *
     * @ORM\Column(name="organizationname", type="string", length=255)
     */
    private $organizationname;

    /**
     * @var string
     *
     * @ORM\Column(name="surname", type="string", length=255)
     */
    private $surname;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="nameprefix", type="string", length=255)
     */
    private $nameprefix;

    /**
     * @var string
     *
     * @ORM\Column(name="middlename", type="string", length=255)
     */
    private $middlename;

    /**
     * @var string
     *
     * @ORM\Column(name="namesuffix", type="string", length=255)
     */
    private $namesuffix;

    /**
     * @var string
     *
     * @ORM\Column(name="nickname", type="string", length=255)
     */
    private $nickname;

    /**
     * @var string
     *
     * @ORM\Column(name="vat", type="string", length=255)
     */
    private $vat;

    /**
     * @var string
     *
     * @ORM\Column(name="tax", type="string", length=255)
     */
    private $tax;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birthdate", type="date")
     */
    private $birthdate;

    /**
     * @var string
     *
     * @ORM\Column(name="photo", type="string", length=255)
     */
    private $photo;

    /**
     * @var string
     *
     * @ORM\Column(name="organizationfunction", type="string", length=255)
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
     * Set contact_id
     *
     * @param integer $contact_id
     * @return Contact
     */
    public function setContactId($contact_id)
    {
        $this->contact_id = $contact_id;

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
     * @param integer $contacttype_id
     * @return Contact
     */
    public function setContacttypeId($contacttype_id)
    {
        $this->contacttype_id = $contacttype_id;

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
     * Set surname
     *
     * @param string $surname
     * @return Contact
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * Get surname
     *
     * @return string 
     */
    public function getSurname()
    {
        return $this->surname;
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
     * Set middlename
     *
     * @param string $middlename
     * @return Contact
     */
    public function setMiddlename($middlename)
    {
        $this->middlename = $middlename;

        return $this;
    }

    /**
     * Get middlename
     *
     * @return string 
     */
    public function getMiddlename()
    {
        return $this->middlename;
    }

    /**
     * Set namesuffix
     *
     * @param string $namesuffix
     * @return Contact
     */
    public function setNamesuffix($namesuffix)
    {
        $this->namesuffix = $namesuffix;

        return $this;
    }

    /**
     * Get namesuffix
     *
     * @return string 
     */
    public function getNamesuffix()
    {
        return $this->namesuffix;
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
     * Set birthdate
     *
     * @param \DateTime $birthdate
     * @return Contact
     */
    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    /**
     * Get birthdate
     *
     * @return \DateTime 
     */
    public function getBirthdate()
    {
        return $this->birthdate;
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
}
