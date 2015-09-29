<?php

namespace Just\CommBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Doctrine\ORM\Query\ResultSetMapping;

use Thruway\ClientSession;
use Thruway\Peer\Client;
use Thruway\Transport\PawlTransportProvider;
use Just\CommBundle\Provider\ChattokenClientAuth;
use Just\UserBundle\Entity\User;
//use React\SocketClient;

class ImapidleCommand extends ContainerAwareCommand {

    private $imapaccounts=Array();
    private $tenant_id=1;
    private $_sessions=Array();
    private $loop;
    
    protected function configure() {
        $this
                ->setName('xxam:imapidle')
                ->setDescription('Connects to Imap Servers and notifies on new emails')
                ->addArgument('tenant_id', InputArgument::REQUIRED, 1)
        
                
        ;
    }
    
    /**
     * Handle on new session joinned
     * 
     * @param array $args
     * @param array $kwArgs
     * @param array $options
     * @return void
     * @link https://github.com/crossbario/crossbar/wiki/Session-Metaevents
     */
    public function onSessionJoin($args, $kwArgs, $options)
    {
        echo "Session  joinned\n";
        $this->_sessions[] = $args[0];
        dump($this->_sessions);
        $this->imapconnectChatuser($args[0]);
    }
    
    /**
     * Handle on session leaved
     * 
     * @param array $args
     * @param array $kwArgs
     * @param array $options
     * @return void
     * @link https://github.com/crossbario/crossbar/wiki/Session-Metaevents
     */
    public function onSessionLeave($args, $kwArgs, $options)
    {
        //dump($args);
        $this->imapdisconnectChatuser($args[0]);
        
        if (!empty($args[0]->session)) {
            foreach ($this->_sessions as $key => $details) {
                if ($args[0]->session == $details->session) {
                    echo "Session {$details->session} leaved\n";
                    unset($this->_sessions[$key]);
                    return;
                }
            }
        }
    }
    
    private function getMailaccountsForChatuser($chatuser){
        $user=$this->em->getRepository('JustUserBundle:User')->findOneByUsername($chatuser->authid);
        if (!$user) return false;
        return $user->getMailaccounts();
    }
    
    private function createImapstream(&$imapaccount){
        //$loop = \React\EventLoop\Factory::create();
        $dnsResolverFactory = new \React\Dns\Resolver\Factory();
        $dns = $dnsResolverFactory->createCached('8.8.8.8', $this->loop);
        $imapconnector = new \React\SocketClient\Connector($this->loop, $dns);
        $secureConnector = new \React\SocketClient\SecureConnector($imapconnector, $this->loop);
        $mailaccount=$imapaccount['mailaccount'];
        echo $mailaccount->getImapserver(). ($mailaccount->getImapport() ? $mailaccount->getImapport() :993);
        $secureConnector->create($mailaccount->getImapserver(), $mailaccount->getImapport() ? $mailaccount->getImapport() :993)->then(function (\React\Stream\Stream $imapstream) use(&$imapaccount) {
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
                            echo $countrecent;
                        }
                    }
                    
                }
             });
            $imapstream->on('end', function () use ($uid, &$status, &$imapstream,&$imapaccount) {
                echo 'END!!!!!';
            });
        });
        
        
        return $imapaccount;
    }
    
    private function imapconnectChatuser($chatuser){
        $mailaccounts=$this->getMailaccountsForChatuser($chatuser);
        if ($mailaccounts){
            foreach($mailaccounts as $mailaccount){
                echo 'X';
                if ($mailaccount->getImapserver()!=''){
                    if (!isset($this->imapaccounts[$mailaccount->getId()])){
                        $this->imapaccounts[$mailaccount->getId()]=Array(
                            'mailaccount'=>$mailaccount,
                            'users'=>Array($chatuser->session),
                            'imapstream'=>null
                        );
                        $this->createImapstream($this->imapaccounts[$mailaccount->getId()]);
                        //$this->imapaccounts[$mailaccount->getId()]['imaploop']->run();
                    }else{
                        $this->imapaccounts[$mailaccount->getId()]['users'][]=$chatuser->session;
                    }
                    dump($this->imapaccounts[$mailaccount->getId()]['users']);
                }
            }
        }   
    }
    
    private function imapdisconnectChatuser($chatuser){
        
        $mailaccounts=$this->getMailaccountsForChatuser($chatuser);
        if ($mailaccounts){
            foreach($mailaccounts as $mailaccount){
                if ($mailaccount->getImapserver()!=''){
                    if (isset($this->imapaccounts[$mailaccount->getId()])){
                        //dump($this->imapaccounts[$mailaccount->getId()]);
                        $key = array_search($chatuser->session, $this->imapaccounts[$mailaccount->getId()]['users']);
                        if($key!==false) unset($this->imapaccounts[$mailaccount->getId()]['users'][$key]);
                    
                        if(count($this->imapaccounts[$mailaccount->getId()]['users'])==0){
                            //dump($this->imapaccounts[$mailaccount->getId()]);
                            $this->imapaccounts[$mailaccount->getId()]['imapstream']->close();
                            //$this->loop->removeStream($this->imapaccounts[$mailaccount->getId()]['imapstream']);
                            
                            unset($this->imapaccounts[$mailaccount->getId()]);
                            echo 'imapstream disconnected...';
                        }
                    }
                }
            }
        }   
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        error_reporting(E_ALL);
        
        $time_start = microtime(true);
        
        
        $container = $this->getApplication()->getKernel()->getContainer();
        $this->em = $container->get('doctrine')->getManager('default');
        //Multiclient Filter:
        $this->tenant_id=$input->getArgument('tenant_id');
        $filter = $this->em->getFilters()->enable('tenant_filter');
        $filter->setParameter('tenant_id', $this->tenant_id);
        $this->loop = \React\EventLoop\Factory::create();
        

        $client = new Client("realm1",$this->loop);
        $client->addClientAuthenticator(new ChattokenClientAuth());
        $client->addTransportProvider(new PawlTransportProvider("ws://127.0.0.1:1337/"));
        $client->on('open', function (ClientSession $session) {
            // 1) subscribe to a topic
            $onevent = function ($args) {
                echo "Event \n";
                dump($args);
            };
            $session->subscribe('com.xxam.imap', $onevent);
            $session->subscribe('wamp.metaevent.session.on_join',  [$this, 'onSessionJoin']);
            $session->subscribe('wamp.metaevent.session.on_leave', [$this, 'onSessionLeave']);
//            // 2) publish an event
//            $session->publish('com.xxam.hello', ['Hello, world from PHP!!!'], [], ["acknowledge" => true])->then(
//                function () {
//                    echo "Publish Acknowledged!\n";
//                },
//                function ($error) {
//                    // publish failed
//                    echo "Publish Error {$error}\n";
//                }
//            );
            // 3) register a procedure for remoting
//            $add2 = function ($args) {
//                return $args[0] + $args[1];
//            };
            //$session->register('com.xxam.add2', $add2);
            // 4) call a remote procedure
            $session->call('com.xxam.getonline', [])->then(
                function ($res) {
                    echo "Result: \n";
                    dump($res->getResultMessage()->getArguments()[0]);
                    foreach($res->getResultMessage()->getArguments()[0] as $chatuser){
                        $this->imapconnectChatuser($chatuser);
                    }
                },
                function ($error) {
                    echo "Call Error: \n";
                }
            );
        });
        $client->start(true);
        //$loop->run();

        
        
        
        
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        $returntext="\n<info>OK in $time Sekunden.</info>";
        $output->writeln($returntext);
    }
    
    

}




?>
