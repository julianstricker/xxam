<?php

namespace Xxam\CalendarBundle\Entity;

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

    }



    
}
