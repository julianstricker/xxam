<?php

namespace Xxam\DynmodBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Xxam\CoreBundle\Entity\Base as Base;

/**
 * Dynmod Formdefinition
 *
 * @ORM\Table(name="dynmod_formdefinition",
 *     indexes={
 *       @ORM\Index(name="ix_tenant_id", columns={"tenant_id"})
 *     })
 * @ORM\Entity(repositoryClass="Xxam\DynmodBundle\Entity\FormdefinitionRepository")
 * @Gedmo\Loggable(logEntryClass="Xxam\CoreBundle\Entity\LogEntry")
 */
class Formdefinition implements Base\TenantInterface
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
    * @ORM\Column(name="roles", type="json_array", nullable=true)
    */
    private $roles;

    /**
     * @var array
     * @Gedmo\Versioned
     * @ORM\Column(name="formfieldsdefinition", type="json_array", nullable=true)
     */
    private $formfieldsdefinition;

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
     * @ORM\ManyToOne(targetEntity="Datacontainer", inversedBy="formdefinitions")
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
     * @return Formdefinition
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
     * Set roles
     *
     * @param array $roles
     *
     * @return Formdefinition
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
     * Set formfieldsdefinition
     *
     * @param array $formfieldsdefinition
     *
     * @return Formdefinition
     */
    public function setFormfieldsdefinition($formfieldsdefinition)
    {
        $this->formfieldsdefinition = $formfieldsdefinition;

        return $this;
    }

    /**
     * Get formfieldsdefinition
     *
     * @return array
     */
    public function getFormfieldsdefinition()
    {
        return $this->formfieldsdefinition;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Formdefinition
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
     * @return Formdefinition
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
     * @return Formdefinition
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
