<?php

namespace XxamMailclientBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Xxam\CoreBundle\Entity\Base as Base;

/**
 * @ORM\Entity
 * @ORM\Table(name="mailaccount",
 *     indexes={
 *       @ORM\Index(name="ix_tenant_id", columns={"tenant_id"})
 *     })
 */
class Mailaccount implements Base\TenantInterface
{
    use Base\TenantTrait;
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="accountname", type="string", length=50)
     */
    protected $accountname;
    
    /**
     * @var string
     *
     * @ORM\Column(name="accountemail", type="string", length=255)
     */
    protected $accountemail;
    
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;
    
    /**
     * @var string
     *
     * @ORM\Column(name="imapserver", type="string", length=255)
     */
    protected $imapserver;
    
    /**
     * @var string
     *
     * @ORM\Column(name="imapport", type="integer")
     */
    protected $imapport=143;
    
    /**
     * @var string
     *
     * @ORM\Column(name="imapusername", type="string", length=255)
     */
    protected $imapusername;
    
    /**
     * @var string
     *
     * @ORM\Column(name="imappassword", type="string", length=255)
     */
    protected $imappassword;
    
    /**
     * @var string
     *
     * @ORM\Column(name="imapsecurity", type="integer")
     */
    protected $imapsecurity=0; //0=off, 1=ssl/tls, 2=ssl/tls alle zertifikate akzeptieren, 3=starttls, 4 starttls alle zertifikate akzeptieren
    
    /**
     * @var string
     *
     * @ORM\Column(name="imappathprefix", type="string", length=255)
     */
    protected $imappathprefix='INBOX';
    
    /**
     * @var string
     *
     * @ORM\Column(name="smtpserver", type="string", length=255)
     */
    protected $smtpserver;
    
    /**
     * @var string
     *
     * @ORM\Column(name="smtpport", type="integer")
     */
    protected $smtpport=25;
    
    /**
     * @var string
     *
     * @ORM\Column(name="smtpusername", type="string", length=255, nullable=true)
     */
    protected $smtpusername;
    
    /**
     * @var string
     *
     * @ORM\Column(name="smtppassword", type="string", length=255, nullable=true)
     */
    protected $smtppassword;
    
    /**
     * @var string
     *
     * @ORM\Column(name="smtpsecurity", type="integer")
     */
    protected $smtpsecurity=0; //0=off, 1=ssl/tls, 2=ssl/tls alle zertifikate akzeptieren, 3=starttls, 4 starttls alle zertifikate akzeptieren
    
    /**
     * @ORM\Column(type="boolean")
     */
    protected $isdefault;
    
    /**
     * @var string
     *
     * @ORM\Column(name="trashfolder", type="string", length=255)
     */
    protected $trashfolder='.Trash';
    
    /**
     * @var string
     *
     * @ORM\Column(name="junkfolder", type="string", length=255)
     */
    protected $junkfolder='.Spam';
    
    /**
     * @var string
     *
     * @ORM\Column(name="sentfolder", type="string", length=255)
     */
    protected $sentfolder='.Sent';
    
    /**
     * @var string
     *
     * @ORM\Column(name="draftfolder", type="string", length=255)
     */
    protected $draftfolder='.Drafts';
    
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
     * @ORM\OneToMany(targetEntity="XxamMailclientBundle\Entity\Mailaccountuser", mappedBy="mailaccount", cascade={"persist", "remove"})
     */
    private $mailaccountusers;

    public function __construct()
    {
        parent::__construct();
        // your own logic
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
     * Set accountname
     *
     * @param string $accountname
     * @return Mailaccount
     */
    public function setAccountname($accountname)
    {
        $this->accountname = $accountname;

        return $this;
    }

    /**
     * Get accountname
     *
     * @return string 
     */
    public function getAccountname()
    {
        return $this->accountname;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Mailaccount
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
     * Set imapserver
     *
     * @param string $imapserver
     * @return Mailaccount
     */
    public function setImapserver($imapserver)
    {
        $this->imapserver = $imapserver;

        return $this;
    }

    /**
     * Get imapserver
     *
     * @return string 
     */
    public function getImapserver()
    {
        return $this->imapserver;
    }

    /**
     * Set imapport
     *
     * @param integer $imapport
     * @return Mailaccount
     */
    public function setImapport($imapport)
    {
        $this->imapport = $imapport;

        return $this;
    }

    /**
     * Get imapport
     *
     * @return integer 
     */
    public function getImapport()
    {
        return $this->imapport;
    }

    /**
     * Set imapusername
     *
     * @param string $imapusername
     * @return Mailaccount
     */
    public function setImapusername($imapusername)
    {
        $this->imapusername = $imapusername;

        return $this;
    }

    /**
     * Get imapusername
     *
     * @return string 
     */
    public function getImapusername()
    {
        return $this->imapusername;
    }

    /**
     * Set imappassword
     *
     * @param string $imappassword
     * @return Mailaccount
     */
    public function setImappassword($imappassword)
    {
        $this->imappassword = $imappassword;

        return $this;
    }

    /**
     * Get imappassword
     *
     * @return string 
     */
    public function getImappassword()
    {
        return $this->imappassword;
    }

    /**
     * Set imapsecurity
     *
     * @param integer $imapsecurity
     * @return Mailaccount
     */
    public function setImapsecurity($imapsecurity)
    {
        $this->imapsecurity = $imapsecurity;

        return $this;
    }

    /**
     * Get imapsecurity
     *
     * @return integer 
     */
    public function getImapsecurity()
    {
        return $this->imapsecurity;
    }

    /**
     * Set imappathprefix
     *
     * @param string $imappathprefix
     * @return Mailaccount
     */
    public function setImappathprefix($imappathprefix)
    {
        $this->imappathprefix = $imappathprefix;

        return $this;
    }

    /**
     * Get imappathprefix
     *
     * @return string 
     */
    public function getImappathprefix()
    {
        return $this->imappathprefix;
    }

    /**
     * Set smtpserver
     *
     * @param string $smtpserver
     * @return Mailaccount
     */
    public function setSmtpserver($smtpserver)
    {
        $this->smtpserver = $smtpserver;

        return $this;
    }

    /**
     * Get smtpserver
     *
     * @return string 
     */
    public function getSmtpserver()
    {
        return $this->smtpserver;
    }

    /**
     * Set smtpport
     *
     * @param integer $smtpport
     * @return Mailaccount
     */
    public function setSmtpport($smtpport)
    {
        $this->smtpport = $smtpport;

        return $this;
    }

    /**
     * Get smtpport
     *
     * @return integer 
     */
    public function getSmtpport()
    {
        return $this->smtpport;
    }

    /**
     * Set smtpusername
     *
     * @param string $smtpusername
     * @return Mailaccount
     */
    public function setSmtpusername($smtpusername)
    {
        $this->smtpusername = $smtpusername;

        return $this;
    }

    /**
     * Get smtpusername
     *
     * @return string 
     */
    public function getSmtpusername()
    {
        return $this->smtpusername;
    }

    /**
     * Set smtppassword
     *
     * @param string $smtppassword
     * @return Mailaccount
     */
    public function setSmtppassword($smtppassword)
    {
        $this->smtppassword = $smtppassword;

        return $this;
    }

    /**
     * Get smtppassword
     *
     * @return string 
     */
    public function getSmtppassword()
    {
        return $this->smtppassword;
    }

    /**
     * Set smtpsecurity
     *
     * @param integer $smtpsecurity
     * @return Mailaccount
     */
    public function setSmtpsecurity($smtpsecurity)
    {
        $this->smtpsecurity = $smtpsecurity;

        return $this;
    }

    /**
     * Get smtpsecurity
     *
     * @return integer 
     */
    public function getSmtpsecurity()
    {
        return $this->smtpsecurity;
    }

    /**
     * Set isdefault
     *
     * @param boolean $isdefault
     * @return Mailaccount
     */
    public function setIsdefault($isdefault)
    {
        $this->isdefault = $isdefault;

        return $this;
    }

    /**
     * Get isdefault
     *
     * @return boolean 
     */
    public function getIsdefault()
    {
        return $this->isdefault;
    }

    /**
     * Set trashfolder
     *
     * @param string $trashfolder
     * @return Mailaccount
     */
    public function setTrashfolder($trashfolder)
    {
        $this->trashfolder = $trashfolder;

        return $this;
    }

    /**
     * Get trashfolder
     *
     * @return string 
     */
    public function getTrashfolder()
    {
        return $this->trashfolder;
    }

    /**
     * Set junkfolder
     *
     * @param string $junkfolder
     * @return Mailaccount
     */
    public function setJunkfolder($junkfolder)
    {
        $this->junkfolder = $junkfolder;

        return $this;
    }

    /**
     * Get junkfolder
     *
     * @return string 
     */
    public function getJunkfolder()
    {
        return $this->junkfolder;
    }

    /**
     * Set sentfolder
     *
     * @param string $sentfolder
     * @return Mailaccount
     */
    public function setSentfolder($sentfolder)
    {
        $this->sentfolder = $sentfolder;

        return $this;
    }

    /**
     * Get sentfolder
     *
     * @return string 
     */
    public function getSentfolder()
    {
        return $this->sentfolder;
    }

    /**
     * Set draftfolder
     *
     * @param string $draftfolder
     * @return Mailaccount
     */
    public function setDraftfolder($draftfolder)
    {
        $this->draftfolder = $draftfolder;

        return $this;
    }

    /**
     * Get draftfolder
     *
     * @return string 
     */
    public function getDraftfolder()
    {
        return $this->draftfolder;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Mailaccount
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
     * @return Mailaccount
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
     * Add mailaccountusers
     *
     * @param \XxamMailclientBundle\Entity\Mailaccountuser $mailaccountusers
     * @return Mailaccount
     */
    public function addMailaccountuser(\XxamMailclientBundle\Entity\Mailaccountuser $mailaccountusers)
    {
        $this->mailaccountusers[] = $mailaccountusers;

        return $this;
    }

    /**
     * Remove mailaccountusers
     *
     * @param \XxamMailclientBundle\Entity\Mailaccountuser $mailaccountusers
     */
    public function removeMailaccountuser(\XxamMailclientBundle\Entity\Mailaccountuser $mailaccountusers)
    {
        $this->mailaccountusers->removeElement($mailaccountusers);
    }

    /**
     * Get mailaccountusers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMailaccountusers()
    {
        return $this->mailaccountusers;
    }

    /**
     * Set accountemail
     *
     * @param string $accountemail
     * @return Mailaccount
     */
    public function setAccountemail($accountemail)
    {
        $this->accountemail = $accountemail;

        return $this;
    }

    /**
     * Get accountemail
     *
     * @return string 
     */
    public function getAccountemail()
    {
        return $this->accountemail;
    }
}
