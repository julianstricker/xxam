<?php

namespace Xxam\ContactBundle\Entity;

use Doctrine\Common\Collections\Criteria;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Xxam\CoreBundle\Entity\Base as Base;

/**
 * Contact
 *
 * @ORM\Table(name="communicationdata",
 *     indexes={
 *       @ORM\Index(name="ix_tenant_id", columns={"tenant_id"})
 *     })
 * @ORM\Entity(repositoryClass="Xxam\ContactBundle\Entity\CommunicationdataRepository")
 * @Gedmo\Loggable(logEntryClass="Xxam\CoreBundle\Entity\LogEntry")
 */
class Communicationdata implements Base\TenantInterface
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
     * @ORM\Column(name="communicationdatatype_id", type="string", nullable=true)
     * @Gedmo\Versioned
     */
    private $communicationdatatype_id;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $value;

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
     * @ORM\ManyToOne(targetEntity="Contact", inversedBy="communicationdatas")
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
     * @return Communicationdata
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
     * Set communicationdatatype_id
     *
     * @param string $communicationdatatypeId
     * @return Communicationdata
     */
    public function setCommunicationdatatypeId($communicationdatatypeId)
    {
        $this->communicationdatatype_id = $communicationdatatypeId;

        return $this;
    }

    /**
     * Get communicationdatatype_id
     *
     * @return string
     */
    public function getCommunicationdatatypeId()
    {
        return $this->communicationdatatype_id;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return Communicationdata
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Communicationdata
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
     * @return Communicationdata
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
     * @return Communicationdata
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
