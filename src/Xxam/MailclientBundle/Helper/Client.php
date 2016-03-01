<?php
namespace Xxam\MailclientBundle\Helper;



use Doctrine\ORM\EntityManager;
//use React\EventLoop\Factory;
use React\Dns\Resolver\Factory;
use React\SocketClient\Connector;
use React\SocketClient\SecureConnector;
use Xxam\CommBundle\Helper\WebSocketClient\WebSocketClient;
use Xxam\CommBundle\Helper\WebSocketClient\WebSocketClientInterface;
use Xxam\MailclientBundle\Entity\Mailaccount;
use Xxam\MailclientBundle\Entity\Mailaccountuser;
use Xxam\MailclientBundle\Entity\MailaccountuserRepository;
use Xxam\UserBundle\Entity\User;
use Xxam\UserBundle\Entity\UserRepository;

class Client implements WebSocketClientInterface
{
    /*
     * Message code
     * @const int
     */
    const MSG_UNKNOWN = 0;
    //const MSG_HELLO = 1;
    const MSG_WELCOME = 2;  //Server -> client, data must contain "id"

    const MSG_ERROR = 8;

    const MSG_PUBLISH = 16;
    //const MSG_PUBLISHED = 17;

    const MSG_SUBSCRIBE = 32;
    const MSG_SUBSCRIBED = 33;
    const MSG_UNSUBSCRIBE = 34;
    const MSG_UNSUBSCRIBED = 35;

    const MSG_GETONLINE = 82;
    const MSG_GETONLINE_RESPONSE = 83;

    const MSG_SUBSCRIBEDBROADCAST = 86;
    const MSG_UNSUBSCRIBEDBROADCAST = 87;

    const MSG_SIGNAL = 96;
    //const MSG_SIGNALED = 97;

    const MSG_VIDEOPHONECALL = 101;
    const MSG_VIDEOPHONECALLACCEPT = 102;
    const MSG_VIDEOPHONECALLCANCEL = 103;


    /* @var WebSocketClient $client */
    private $client;
    private $tenant_id=1;
    private $memcached;
    /* @var EntityManager $em */
    private $em;

    private $imapaccounts=Array();
    private $_sessionids=Array();
    private $loop;

    /**
     * Client constructor.
     * @param int $tenant_id
     * @param EntityManager $em
     */
    public function __construct($tenant_id, EntityManager $em,$loop)
    {
        $this->tenant_id=$tenant_id;
        $this->em=$em;
        $this->memcached = $this->get('memcached');
        $this->loop = $loop;

    }

    public function receiveData($data)
    {
        echo "\n".'receivedData:';
        dump($data);
        if (isset($data[0])) {
            switch ($data[0]) {
                case self::MSG_WELCOME:
                    $this->onWelcome($data);
                    break;
                case self::MSG_SUBSCRIBEDBROADCAST:
                    $this->onSubscribedbroadcast($data);
                    break;
                case self::MSG_UNSUBSCRIBEDBROADCAST:
                    $this->onUnsubscribedbroadcast($data);
                    break;
                case self::MSG_SUBSCRIBED:
                    $this->onSubscribed($data);
                    break;
                case self::MSG_GETONLINE_RESPONSE:
                    $this->onGetonlineResponse($data);
                    break;

            }
        }
    }


    public function onWelcome(array $data)
    {
        $sessionid=$data[1]['id'];
        $storedata=array(
            'tenant_id'=>$this->tenant_id,
            'user_id'=>null,
            'username'=>'Imapidle',
            'sessionid'=>$sessionid
        );

        $this->memcached->add('chatsessionid_'.$sessionid,$storedata);
        dump($storedata);
        $this->subscribe('com.xxam.imap');
        //$this->client->send([self::MSG_GETONLINE,["topic"=>'com.xxam.imap']]);
    }

    public function onUnsubscribedbroadcast(array $data)
    {
        echo "\n".'onUnsubscribedbroadcast';

        foreach ($data[1]['com.xxam.imap'] as $sessionid => $username){
            $this->imapdisconnectChatuser($sessionid);
            foreach ($this->_sessionids as $key=>$sid) {
                if ($sessionid == $sid) {
                    echo "Session {$sid} leaved\n";
                    unset($this->_sessionids[$key]);
                    return;
                }
            }
        }


    }

    public function onSubscribedbroadcast(array $data)
    {
        echo "\n".'onSubscribedbroadcast';
        foreach ($data[1]['com.xxam.imap'] as $sessionid => $username){
            $this->imapconnectChatuser($sessionid);
            $this->_sessionids[]=$sessionid;
        }
    }

    public function onSubscribed(array $data){
        //getonline:
        /* @var WebSocketClient $client */
        $this->client->send([self::MSG_GETONLINE,["topic"=>'com.xxam.imap']]);
    }


    public function onGetonlineResponse(array $data)
    {
        echo 'onGetonlineResponse';
        dump($data[1]['com.xxam.imap']);
        foreach ($data[1]['com.xxam.imap'] as $sessionid => $username){
                $this->imapconnectChatuser($sessionid);
        }
    }

    public function onEvent($topic, $message)
    {
        echo 'onEvent';
    }

    public function subscribe($topic)
    {
        $this->client->subscribe(["topic"=>$topic]);
    }

    public function unsubscribe($topic)
    {
        $this->client->unsubscribe($topic);
    }

    public function call($proc, $args, \Closure $callback = null)
    {
        $this->client->call($proc, $args, $callback);
    }

    public function publish($topic, $message, array $eligible = array())
    {
        //publish($topicUri, $event, array $exclude = array(), array $eligible = array())
        $this->client->publish($topic, $message, [], $eligible);
    }

    public function setClient(WebSocketClient $client)
    {
        $this->client = $client;
        echo 'setclient';
    }

    /**
     * @param String $sessionid
     * @return mixed
     */
    private function getUserdataForSessionid($sessionid){
        return $this->memcached->get('chatsessionid_'.$sessionid);
    }



    private function imapconnectChatuser($sessionid){
        echo 'aa';
        $mailaccounts=$this->getMailaccountsForSessionid($sessionid);
        echo 'xx';
        if ($mailaccounts){
            foreach($mailaccounts as $mailaccount){
                echo 'X';
                if ($mailaccount->getImapserver()!=''){
                    if (!isset($this->imapaccounts[$mailaccount->getId()])){
                        $this->imapaccounts[$mailaccount->getId()]=Array(
                            'mailaccount'=>$mailaccount,
                            'users'=>Array($sessionid),
                            'imapstream'=>null
                        );
                        $this->createImapstream($this->imapaccounts[$mailaccount->getId()]);
                        //$this->imapaccounts[$mailaccount->getId()]['imaploop']->run();
                    }
                    $this->imapaccounts[$mailaccount->getId()]['users'][]=$sessionid;

                    dump($this->imapaccounts[$mailaccount->getId()]['users']);
                }
            }
        }
    }

    private function imapdisconnectChatuser($sessionid){

        $mailaccounts=$this->getMailaccountsForSessionid($sessionid);
        if ($mailaccounts){
            foreach($mailaccounts as $mailaccount){
                if ($mailaccount->getImapserver()!=''){
                    if (isset($this->imapaccounts[$mailaccount->getId()])){
                        dump($this->imapaccounts[$mailaccount->getId()]);
                        $key = array_search($sessionid, $this->imapaccounts[$mailaccount->getId()]['users']);
                        if($key!==false) unset($this->imapaccounts[$mailaccount->getId()]['users'][$key]);

                        if(count($this->imapaccounts[$mailaccount->getId()]['users'])==0){
                            echo '$this->imapaccounts[$mailaccount->getId()]';
                            dump($this->imapaccounts[$mailaccount->getId()]);
                            if($this->imapaccounts[$mailaccount->getId()]['imapstream'])$this->imapaccounts[$mailaccount->getId()]['imapstream']->close();
                            //$this->loop->removeStream($this->imapaccounts[$mailaccount->getId()]['imapstream']);

                            unset($this->imapaccounts[$mailaccount->getId()]);
                            echo 'imapstream disconnected...';
                        }
                    }
                }
            }
        }
    }


    /*todo ...*/
    private function notifychanges(&$imapaccount,$data){
        dump($imapaccount['users']);
        $this->publish("com.xxam.imap", $data,array_values($imapaccount['users']));
    }

    /**
     * @param $sessionid
     * @return Mailaccount[]|bool
     */
    private function getMailaccountsForSessionid($sessionid){
        $userdata=$this->getUserdataForSessionid($sessionid);
        /** @var UserRepository $userrepository */
        $userrepository=$this->em->getRepository('XxamUserBundle:User');
        /** @var User $user */
        $user=$userrepository->findOneByUsername($userdata['username']);
        if (!$user) return false;
        /** @var MailaccountuserRepository $repository */
        $repository=$this->em->getRepository('XxamMailclientBundle:Mailaccountuser');
        $mailaccountusers=$repository->findByUserId($user->getId());
        $mailaccounts=false;
        /** @var Mailaccountuser $mau */
        foreach ($mailaccountusers as $mau){
            $mailaccounts[]=$mau->getMailaccount();
        }
        return $mailaccounts;
    }


    private function createImapstream(&$imapaccount){
        echo 'createImapstream';
        //$loop = \React\EventLoop\Factory::create();
        $dnsResolverFactory = new Factory();
        $dns = $dnsResolverFactory->createCached('8.8.8.8', $this->loop);

        $imapconnector = new Connector($this->loop, $dns);

        /** @var Mailaccount $mailaccount */
        $mailaccount=$imapaccount['mailaccount'];
        $imapport=$mailaccount->getImapport() ? $mailaccount->getImapport() :993;
        //$imapport=143;
        if ($imapport==993){ //secure
            $secureConnector = new SecureConnector($imapconnector, $this->loop);
            $conn= $secureConnector->create($mailaccount->getImapserver(), $imapport);
        }else{
            $conn=$imapconnector->create($mailaccount->getImapserver(), $imapport);
        }

        echo $mailaccount->getImapserver(). $imapport;
        $conn->then(function (\React\Stream\Stream $imapstream) use(&$imapaccount) {

            echo 'Jojo';
            $uid=uniqid();
            echo $imapaccount['mailaccount']->getImapserver().': '.$uid;

            $imapaccount['imapstream']=$imapstream;
            $login=$imapaccount['mailaccount']->getImapusername();
            $password=$imapaccount['mailaccount']->getImappassword();
            $imapstream->write($uid." LOGIN $login $password\r\n");
            $status='LOGIN';
            $imapstream->on('data',function($data) use ($uid, &$status, &$imapstream,&$imapaccount){
                echo $imapaccount['mailaccount']->getImapserver().': '.($data);
                $dataexpl=explode("\r\n",$data);
                foreach($dataexpl as $dexpl){
                    if (preg_match("/^".$uid." OK/", $dexpl)){ //login OK:
                        if ($status=='LOGIN'){

                            $imapstream->write($uid." SELECT ".$imapaccount['mailaccount']->getImappathprefix()."\r\n");
                            $status='SELECT';
                            echo "SEND: SELECT $status\r\n";
                        }else if ($status=='SELECT'){

                            $imapstream->write($uid." IDLE\r\n");
                            $status='IDLE';
                            echo "SEND: IDLE $status\r\n";
                        }
                    }
                    if ($status=='IDLE'){
                        if(preg_match("/^\* (\d+) RECENT/", $dexpl,$countrecent)){ //login OK:
                            $countrecent=$countrecent[1];
                            echo 'RECENT:'.$countrecent;
                            $this->notifychanges($imapaccount,['mailaccount_id'=>$imapaccount['mailaccount']->getId(),'recent'=>$countrecent]);
                        }
                        if(preg_match("/^\* (\d+) EXISTS/", $dexpl,$countexists)){ //login OK:
                            $countexists=$countexists[1];
                            echo 'EXISTS'.$countexists;
                            $this->notifychanges($imapaccount,['mailaccount_id'=>$imapaccount['mailaccount']->getId(),'exists'=>$countexists]);
                        }
                    }

                }
            });
            $imapstream->on('end', function () use ($uid, &$status, &$imapstream,&$imapaccount) {
                echo 'END!!!!!';
                $this->createImapstream($imapaccount);
            });
        },
        function ($error)  use(&$imapaccount) {
            echo "Call Error: \n";
            dump($error->getMessage());
            echo "trying again...\n";
            $this->createImapstream($imapaccount);
        });


        return $imapaccount;
    }

}
