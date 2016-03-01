<?php

namespace Xxam\DynmodBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Xxam\CoreBundle\Entity\Base as Base;

/**
 * Dynmod Data
 *
 * @ORM\Table(name="dynmod_data",
 *     indexes={
 *       @ORM\Index(name="ix_tenant_id", columns={"tenant_id"})
 *     })
 * @ORM\Entity(repositoryClass="Xxam\DynmodBundle\Entity\DataRepository")
 * @Gedmo\Loggable(logEntryClass="Xxam\CoreBundle\Entity\LogEntry")
 */
class Data implements Base\TenantInterface
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
     * @Gedmo\Versioned
     * @ORM\Column(name="datacontainer_id", type="integer", nullable=true)
     */
    private $datacontainer_id;

    /**
    * @var array
    * @Gedmo\Versioned
    * @ORM\Column(name="values", type="json_array", nullable=false)
    */
    private $values;

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
     * @ORM\ManyToOne(targetEntity="Datacontainer", inversedBy="datas")
     * @ORM\JoinColumn(name="datacontainer_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $datacontainer;

    


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
     * Set datacontainerId
     *
     * @param integer $datacontainerId
     *
     * @return Data
     */
    public function setDatacontainerId($datacontainerId)
    {
        $this->datacontainer_id = $datacontainerId;

        return $this;
    }

    /**
     * Get datacontainerId
     *
     * @return integer
     */
    public function getDatacontainerId()
    {
        return $this->datacontainer_id;
    }

    /**
     * Set values
     *
     * @param array $values
     *
     * @return Data
     */
    public function setValues($values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * Get values
     *
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Data
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
     *
     * @return Data
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
     * Set datacontainer
     *
     * @param Datacontainer $datacontainer
     *
     * @return Data
     */
    public function setDatacontainer(Datacontainer $datacontainer = null)
    {
        $this->datacontainer = $datacontainer;

        return $this;
    }

    /**
     * Get datacontainer
     *
     * @return Datacontainer
     */
    public function getDatacontainer()
    {
        return $this->datacontainer;
    }
}
