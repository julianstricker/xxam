<?php

namespace Xxam\DynmodBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Xxam\CoreBundle\Entity\Base as Base;

/**
 * Dynmod Datacontainer
 *
 * @ORM\Table(name="dynmod_datacontainer",
 *     indexes={
 *       @ORM\Index(name="ix_tenant_id", columns={"tenant_id"})
 *     })
 * @ORM\Entity(repositoryClass="Xxam\DynmodBundle\Entity\DatacontainerRepository")
 * @Gedmo\Loggable(logEntryClass="Xxam\CoreBundle\Entity\LogEntry")
 */
class Datacontainer implements Base\TenantInterface
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=80, nullable=false)
     * @Gedmo\Versioned
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="defaultcontainer", type="boolean")
     * @Gedmo\Versioned
     */
    private $defaultcontainer=false;

    /**
     * @var array
     *
     * @ORM\Column(name="fielddefinitions", type="json_array", nullable=true)
     * @Gedmo\Versioned
     */
    private $fielddefinitions;

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
     * @ORM\ManyToOne(targetEntity="Dynmod", inversedBy="datacontainers")
     * @ORM\JoinColumn(name="dynmod_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $dynmod;



    /**
     * @ORM\OneToMany(targetEntity="Data", mappedBy="datacontainer")
     */
    private $datas;

    /**
     * @ORM\OneToMany(targetEntity="Formdefinition", mappedBy="datacontainer")
     */
    private $formdefinitions;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->datas = new ArrayCollection();
        $this->formdefinitions = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     *
     * @return Datacontainer
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set fielddefinitions
     *
     * @param array $fielddefinitions
     *
     * @return Datacontainer
     */
    public function setFielddefinitions($fielddefinitions)
    {
        $this->fielddefinitions = $fielddefinitions;

        return $this;
    }

    /**
     * Get fielddefinitions
     *
     * @return array
     */
    public function getFielddefinitions()
    {
        return $this->fielddefinitions;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Datacontainer
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
     * @return Datacontainer
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
     * Add data
     *
     * @param Data $data
     *
     * @return Datacontainer
     */
    public function addData(Data $data)
    {
        $this->datas[] = $data;

        return $this;
    }

    /**
     * Remove data
     *
     * @param Data $data
     */
    public function removeData(Data $data)
    {
        $this->datas->removeElement($data);
    }

    /**
     * Get datas
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDatas()
    {
        return $this->datas;
    }

    /**
     * Add formdefinition
     *
     * @param Formdefinition $formdefinition
     *
     * @return Datacontainer
     */
    public function addFormdefinition(Formdefinition $formdefinition)
    {
        $this->formdefinitions[] = $formdefinition;

        return $this;
    }

    /**
     * Remove formdefinition
     *
     * @param Formdefinition $formdefinition
     */
    public function removeFormdefinition(Formdefinition $formdefinition)
    {
        $this->formdefinitions->removeElement($formdefinition);
    }

    /**
     * Get formdefinitions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFormdefinitions()
    {
        return $this->formdefinitions;
    }

    /**
     * Set dynmod
     *
     * @param Dynmod $dynmod
     *
     * @return Datacontainer
     */
    public function setDynmod(Dynmod $dynmod = null)
    {
        $this->dynmod = $dynmod;

        return $this;
    }

    /**
     * Get dynmod
     *
     * @return Dynmod
     */
    public function getDynmod()
    {
        return $this->dynmod;
    }

    /**
     * Set defaultcontainer
     *
     * @param boolean $defaultcontainer
     *
     * @return Datacontainer
     */
    public function setDefaultcontainer($defaultcontainer)
    {
        $this->defaultcontainer = $defaultcontainer;

        return $this;
    }

    /**
     * Get defaultcontainer
     *
     * @return boolean
     */
    public function isDefaultcontainer()
    {
        return $this->defaultcontainer;
    }

    public function toGridObject($timezone='') {
        if($timezone=='') $timezone=date_default_timezone_get();
        return Array(
            'id' =>                     $this->getId(),
            'name' =>                   $this->getName(),
            'defaultcontainer' =>       $this->isDefaultcontainer(),
            'created' =>                $this->getCreated() ? $this->getCreated()->setTimezone(new \DateTimeZone($timezone))->format('Y-m-d H:i:s') : false,
            'updated' =>                $this->getUpdated() ? $this->getUpdated()->setTimezone(new \DateTimeZone($timezone))->format('Y-m-d H:i:s') : false
        );
    }

    public function getModelFields(){

        $fields=Array();
        if ($this->getFielddefinitions()) {
            foreach ($this->getFielddefinitions() as $fielddefinition) {
                $field = [];
                $field['name'] = $fielddefinition['name'];
                $field['type'] = $fielddefinition['type'];
                $field = array_merge($field, $fielddefinition['modelconfig']);
                $fields[] = $field;

            }
        }
        return $fields;

    }
    public function getGridColumns(){
        $columns=Array();
        if ($this->getFielddefinitions()) {
            foreach ($this->getFielddefinitions() as $fielddefinition) {
                $column = [];
                $column['text'] = $fielddefinition['text'];
                $column['dataIndex'] = $fielddefinition['name'];
                $column['type'] = $fielddefinition['type'];
                $column = array_merge($column, $fielddefinition['formfieldconfig']);
                $columns[] = $column;
            }
        }
        return $columns;

    }

}
