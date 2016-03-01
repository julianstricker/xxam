<?php

namespace Xxam\ContactBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Xxam\CoreBundle\Entity\Base as Base;

/**
 * Contact
 *
 * @ORM\Table(name="address",
 *     indexes={
 *       @ORM\Index(name="ix_tenant_id", columns={"tenant_id"})
 *     })
 * @ORM\Entity(repositoryClass="Xxam\ContactBundle\Entity\AddressRepository")
 * @Gedmo\Loggable(logEntryClass="Xxam\CoreBundle\Entity\LogEntry")
 */
class Address implements Base\TenantInterface
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
     * @ORM\Column(name="addresstype_id", type="integer", nullable=true)
     * @Gedmo\Versioned
     */
    private $addresstype_id;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=511, nullable=true)
     * @Gedmo\Versioned
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="countrycode", type="string", length=5, nullable=true)
     * @Gedmo\Versioned
     */
    private $countrycode;

    /**
     * @var string
     *
     * @ORM\Column(name="region", type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $region;

    /**
     * @var string
     *
     * @ORM\Column(name="zip", type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $zip;

    /**
     * @var string
     *
     * @ORM\Column(name="locality", type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $locality;

    /**
     * @var string
     *
     * @ORM\Column(name="timezone", type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $timezone;
    
    /**
     * @ORM\Column(type="decimal", scale=5, nullable=true)
     * @Gedmo\Versioned
     */
    private $lat;

    /**
     * @ORM\Column(type="decimal", scale=5, nullable=true)
     * @Gedmo\Versioned
     */
    private $lng;

    /**
     * @var string
     *
     * @ORM\Column(name="tax", type="float", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $geolat;

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
     * @ORM\ManyToOne(targetEntity="Contact", inversedBy="addresses")
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $contact;

    

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
     * @return Address
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
     * Set addresstype_id
     *
     * @param integer $addresstypeId
     * @return Address
     */
    public function setAddresstypeId($addresstypeId)
    {
        $this->addresstype_id = $addresstypeId;

        return $this;
    }

    /**
     * Get addresstype_id
     *
     * @return integer 
     */
    public function getAddresstypeId()
    {
        return $this->addresstype_id;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return Address
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string 
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set countrycode
     *
     * @param string $countrycode
     * @return Address
     */
    public function setCountrycode($countrycode)
    {
        $this->countrycode = $countrycode;

        return $this;
    }

    /**
     * Get countrycode
     *
     * @return string 
     */
    public function getCountrycode()
    {
        return $this->countrycode;
    }

    /**
     * Set region
     *
     * @param string $region
     * @return Address
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region
     *
     * @return string 
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set zip
     *
     * @param string $zip
     * @return Address
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip
     *
     * @return string 
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set locality
     *
     * @param string $locality
     * @return Address
     */
    public function setLocality($locality)
    {
        $this->locality = $locality;

        return $this;
    }

    /**
     * Get locality
     *
     * @return string 
     */
    public function getLocality()
    {
        return $this->locality;
    }

    /**
     * Set timezone
     *
     * @param string $timezone
     * @return Address
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Get timezone
     *
     * @return string 
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Set lat
     *
     * @param string $lat
     * @return Address
     */
    public function setLat($lat)
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * Get lat
     *
     * @return string 
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lng
     *
     * @param string $lng
     * @return Address
     */
    public function setLng($lng)
    {
        $this->lng = $lng;

        return $this;
    }

    /**
     * Get lng
     *
     * @return string 
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Set geolat
     *
     * @param float $geolat
     * @return Address
     */
    public function setGeolat($geolat)
    {
        $this->geolat = $geolat;

        return $this;
    }

    /**
     * Get geolat
     *
     * @return float 
     */
    public function getGeolat()
    {
        return $this->geolat;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Address
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
     * @return Address
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
     * Set contact
     *
     * @param \Xxam\ContactBundle\Entity\Contact $contact
     * @return Address
     */
    public function setContact(\Xxam\ContactBundle\Entity\Contact $contact = null)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return \Xxam\ContactBundle\Entity\Contact
     */
    public function getContact()
    {
        return $this->contact;
    }
}
