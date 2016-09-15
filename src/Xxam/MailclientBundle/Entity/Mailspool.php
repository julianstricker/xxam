<?php

namespace Xxam\MailclientBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Xxam\CoreBundle\Entity\Base as Base;
use Xxam\UserBundle\Entity\User;




/**
 * @ORM\Entity(repositoryClass="Xxam\MailclientBundle\Entity\MailspoolRepository")
 * @ORM\Table(
 *     name="mailspool",
 *     indexes={
 *       @ORM\Index(name="ix_tenant_id", columns={"tenant_id"}),
 *       @ORM\Index(name="ix_sendtime", columns={"sendtime"}),
 *       @ORM\Index(name="ix_sendafter", columns={"sendafter"}),
 *       @ORM\Index(columns={"sendstatus"}),
 *       @ORM\Index(columns={"created"}),
 *       @ORM\Index(columns={"updated"})
 *     }
 * )
 */
class Mailspool implements Base\TenantInterface
{
    use Base\TenantTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $mailaccount_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $user_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $subject;

    /**
     * @ORM\Column(type="json_array")
     */
    private $emailto;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $emailreplyto;

    /**
     * @ORM\Column(type="json_array")
     */
    private $emailfrom;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $emailcc;

    /**
     * @ORM\Column(type="json_array",nullable=true)
     */
    private $emailbcc;


    /**
     * @ORM\Column(type="blob", nullable=true)
     */
    private $message;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $sendafter;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $sendtime;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $readtimes;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $tracking;

    /**
     * @ORM\Column(type="integer", length=1)
     */
    private $sendstatus = 0;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $sendlog = '';

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
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */

    private $updated;

    /**
     * @ORM\ManyToOne(targetEntity="Mailaccount", inversedBy="mailspools")
     * @ORM\JoinColumn(name="mailaccount_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $mailaccount;

    /**
     * @ORM\ManyToOne(targetEntity="Xxam\UserBundle\Entity\User", inversedBy="mailspools")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;


    public function toArray($timezone='') {
        return $this->toGridObject($timezone);
    }

    public function toGridObject($timezone='') {
        if($timezone=='') $timezone=date_default_timezone_get();

        return Array(
            'id' => $this->id,
            'mailaccount_id' => $this->getMailaccount()->getId(),
            'user_id' => $this->getUserId(),
            'subject' => $this->getSubject(),
            'emailfrom' => $this->getEmailfrom(),
            'emailto' => $this->getEmailto(),
            'emailcc' => $this->getEmailcc(),
            'emailbcc' => $this->getEmailbcc(),
            'sendafter' => is_null($this->getSendafter()) ? '-' : $this->getSendafter()->setTimezone(new \DateTimeZone($timezone))->format('Y-m-d H:i:s'),
            'sendtime' => is_null($this->getSendtime()) ? '-' : $this->getSendtime()->setTimezone(new \DateTimeZone($timezone))->format('Y-m-d H:i:s'),
            'readtimes' => $this->getReadtimes(),
            'updated' => is_null($this->getUpdated()) ? '-' : $this->getUpdated()->setTimezone(new \DateTimeZone($timezone))->format('Y-m-d H:i:s'),
            'created' => is_null($this->getCreated()) ? '-' : $this->getCreated()->setTimezone(new \DateTimeZone($timezone))->format('Y-m-d H:i:s'),
            'sendstatus' => $this->getSendstatus()
        );
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
     * Set subject
     *
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set emailto
     *
     * @param array $emailto
     */
    public function setEmailto($emailto)
    {
        $this->emailto = $emailto;
    }

    /**
     * Get emailto
     *
     * @return array
     */
    public function getEmailto()
    {
        return $this->emailto;
    }

    /**
     * Set emailreplyto
     *
     * @param array $emailreplyto
     */
    public function setEmailreplyto($emailreplyto)
    {
        $this->emailreplyto = $emailreplyto;
    }

    /**
     * Get emailreplyto
     *
     * @return array
     */
    public function getEmailreplyto()
    {
        return $this->emailreplyto;
    }

    /**
     * Set emailfrom
     *
     * @param array $emailfrom
     */
    public function setEmailfrom($emailfrom)
    {
        $this->emailfrom = $emailfrom;
    }

    /**
     * Get emailfrom
     *
     * @return array
     */
    public function getEmailfrom()
    {
        return $this->emailfrom;
    }

    /**
     * Set emailcc
     *
     * @param array $emailcc
     */
    public function setEmailcc($emailcc)
    {
        $this->emailcc = $emailcc;
    }

    /**
     * Get emailcc
     *
     * @return array
     */
    public function getEmailcc()
    {
        return $this->emailcc;
    }

    /**
     * Set emailbcc
     *
     * @param array $emailbcc
     */
    public function setEmailbcc($emailbcc)
    {
        $this->emailbcc = $emailbcc;
    }

    /**
     * Get emailbcc
     *
     * @return array
     */
    public function getEmailbcc()
    {
        return $this->emailbcc;
    }

    /**
     * Set sendafter
     *
     * @param \DateTime $sendafter
     */
    public function setSendafter($sendafter)
    {
        $this->sendafter = $sendafter;
    }

    /**
     * Get sendafter
     *
     * @return \DateTime
     */
    public function getSendafter()
    {
        return $this->sendafter;
    }

    /**
     * Set sendtime
     *
     * @param \DateTime $sendtime
     */
    public function setSendtime($sendtime)
    {
        $this->sendtime = $sendtime;
    }

    /**
     * Get sendtime
     *
     * @return \DateTime
     */
    public function getSendtime()
    {
        return $this->sendtime;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
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
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
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
     * @return int
     */
    public function getSendstatus()
    {
        return $this->sendstatus;
    }

    /**
     * @param int $sendstatus
     */
    public function setSendstatus($sendstatus)
    {
        $this->sendstatus = $sendstatus;
    }

    /**
     * @return string
     */
    public function getSendlog()
    {
        return $this->sendlog;
    }

    /**
     * @param string $sendlog
     */
    public function setSendlog($sendlog)
    {
        $this->sendlog = $sendlog;
    }

    /**
     * @return int
     */
    public function getMailaccountId()
    {
        return $this->mailaccount_id;
    }

    /**
     * @param int $mailaccount_id
     */
    public function setMailaccountuserId($mailaccount_id)
    {
        $this->mailaccount_id = $mailaccount_id;
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param string $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }


    /**
     * @return array
     */
    public function getReadtimes()
    {
        return $this->readtimes;
    }

    /**
     * @param array $readtimes
     */
    public function setReadtimes($readtimes)
    {
        $this->readtimes = $readtimes;
    }

    /**
     * @return array
     */
    public function getTracking()
    {
        return $this->tracking;
    }

    /**
     * @param array $tracking
     */
    public function setTracking($tracking)
    {
        $this->tracking = $tracking;
    }

    /**
     * @return Mailaccount
     */
    public function getMailaccount()
    {
        return $this->mailaccount;
    }

    /**
     * @param Mailaccount $mailaccount
     */
    public function setMailaccount(Mailaccount $mailaccount)
    {
        $this->mailaccount = $mailaccount;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return Mailspool
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return \Swift_Message
     */
    public function getMessage()
    {
        return unserialize(stream_get_contents($this->message));
    }

    /**
     * @param \Swift_Message $message
     */
    public function setMessage(\Swift_Message $message)
    {
        $this->message = serialize($message);
    }

    /**
     * @param \Swift_Message $message
     * @param User $user
     * @param Mailaccount $mailaccount
     */
    public function mapMessage(\Swift_Message $message, User $user, Mailaccount $mailaccount){
        $this->setMessage($message);
        $this->setUser($user);
        $this->setMailaccount($mailaccount);
        $this->setEmailbcc($message->getBcc());
        $this->setEmailcc($message->getCc());
        $this->setEmailfrom($message->getFrom());
        $this->setEmailreplyto($message->getReplyTo());
        $this->setEmailto($message->getTo());
        $this->setSubject($message->getSubject());
        $this->setSendstatus(0);
    }
}