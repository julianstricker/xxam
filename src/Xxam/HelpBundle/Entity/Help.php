<?php

namespace Xxam\HelpBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Xxam\CoreBundle\Entity\Base as Base;

/**
 * Help Help
 *
 * @ORM\Table(name="help",
 *     indexes={
 *       @ORM\Index(name="ix_tenant_id", columns={"tenant_id"})
 *     })
 * @ORM\Entity(repositoryClass="Xxam\HelpBundle\Entity\HelpRepository")
 * @Gedmo\Loggable(logEntryClass="Xxam\CoreBundle\Entity\LogEntry")
 * @UniqueEntity("code")
 */
class Help implements Base\TenantInterface
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
     * @var array
     *
     * @ORM\Column(name="roles", type="json_array", nullable=true)
     * @Gedmo\Versioned
     */
    private $roles;

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
     * Set name
     *
     * @param string $name
     *
     * @return Help
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
     * @return Help
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
     * @return Help
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
     * Set roles
     *
     * @param array $roles
     *
     * @return Help
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get roles
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Set actions
     *
     * @param array $actions
     *
     * @return Help
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
     * @return Help
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
     * @return Help
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
     * @return Help
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


    /**
     * Set code
     *
     * @param string $code
     *
     * @return Help
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
}
