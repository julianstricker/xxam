<?php

namespace Xxam\DynmodBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Xxam\CoreBundle\Entity\Base as Base;

/**
 * Dynmod Dynmod
 *
 * @ORM\Table(name="dynmod",
 *     indexes={
 *       @ORM\Index(name="ix_tenant_id", columns={"tenant_id"})
 *     })
 * @ORM\Entity(repositoryClass="Xxam\DynmodBundle\Entity\DynmodRepository")
 * @Gedmo\Loggable(logEntryClass="Xxam\CoreBundle\Entity\LogEntry")
 * @UniqueEntity("code")
 */
class Dynmod implements Base\TenantInterface
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
     * @ORM\Column(name="code", type="string", length=50, nullable=false)
     * @Gedmo\Versioned
     * @Assert\NotBlank()
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=80, nullable=false)
     * @Gedmo\Versioned
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=511, nullable=true)
     * @Gedmo\Versioned
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="help", type="string", length=511, nullable=true)
     * @Gedmo\Versioned
     */
    private $help;

    /**
     * @var string
     *
     * @ORM\Column(name="iconcls", type="string", length=150, nullable=true)
     * @Gedmo\Versioned
     */
    private $iconcls;

    /**
     * @var array
     *
     * @ORM\Column(name="additionalroles", type="json_array", nullable=true)
     * @Gedmo\Versioned
     */
    private $additionalroles;

    /**
     * @var array
     *
     * @ORM\Column(name="actions", type="json_array", nullable=true)
     * @Gedmo\Versioned
     */
    private $actions;

    /**
     * @var array
     *
     * @ORM\Column(name="objectactions", type="json_array", nullable=true)
     * @Gedmo\Versioned
     */
    private $objectactions;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean")
     * @Gedmo\Versioned
     */
    private $active=0;

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
     * @ORM\OneToMany(targetEntity="Datacontainer", mappedBy="dynmod")
     */
    private $datacontainers;


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
     * @return Dynmod
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
     * Set description
     *
     * @param string $description
     *
     * @return Dynmod
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set help
     *
     * @param string $help
     *
     * @return Dynmod
     */
    public function setHelp($help)
    {
        $this->help = $help;

        return $this;
    }

    /**
     * Get help
     *
     * @return string
     */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * Set additionalroles
     *
     * @param array $additionalroles
     *
     * @return Dynmod
     */
    public function setAdditionalroles($additionalroles)
    {
        $this->additionalroles = $additionalroles;

        return $this;
    }

    /**
     * Get additionalroles
     *
     * @return array
     */
    public function getAdditionalroles()
    {
        return $this->additionalroles;
    }

    /**
     * Set actions
     *
     * @param array $actions
     *
     * @return Dynmod
     */
    public function setActions($actions)
    {
        $this->actions = $actions;

        return $this;
    }

    /**
     * Get actions
     *
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * Set objectactions
     *
     * @param array $objectactions
     *
     * @return Dynmod
     */
    public function setObjectactions($objectactions)
    {
        $this->objectactions = $objectactions;

        return $this;
    }

    /**
     * Get objectactions
     *
     * @return array
     */
    public function getObjectactions()
    {
        return $this->objectactions;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Dynmod
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
     * @return Dynmod
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
        $this->datacontainers = new ArrayCollection();
    }

    /**
     * Add datacontainer
     *
     * @param Datacontainer $datacontainer
     *
     * @return Dynmod
     */
    public function addDatacontainer(Datacontainer $datacontainer)
    {
        $this->datacontainers[] = $datacontainer;

        return $this;
    }

    /**
     * Remove datacontainer
     *
     * @param Datacontainer $datacontainer
     */
    public function removeDatacontainer(Datacontainer $datacontainer)
    {
        $this->datacontainers->removeElement($datacontainer);
    }


    /**
     * Set code
     *
     * @param string $code
     *
     * @return Dynmod
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     * @return Dynmod
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDatacontainers()
    {
        return $this->datacontainers;
    }

    /**
     * @param mixed $datacontainers
     * @return Dynmod
     */
    public function setDatacontainers($datacontainers)
    {
        $this->datacontainers = $datacontainers;
        return $this;
    }

    /**
     * @return string
     */
    public function getIconcls()
    {
        return $this->iconcls;
    }

    /**
     * @param string $iconcls
     * @return Dynmod
     */
    public function setIconcls($iconcls)
    {
        $this->iconcls = $iconcls;
        return $this;
    }

    public function toGridObject() {
        return Array(
            'id' =>                     $this->getId(),
            'code' =>                   $this->getCode(),
            'name' =>                   $this->getName(),
            'description' =>            $this->getDescription(),
            'help' =>                   $this->getHelp(),
            'active' =>                 $this->isActive(),
            'iconcls' =>                $this->getIconcls(),
            'created' =>                $this->getCreated() ? $this->getCreated()->format('Y-m-d H:i:s') : false,
            'updated' =>                $this->getUpdated() ? $this->getUpdated()->format('Y-m-d H:i:s') : false
        );
    }

}
