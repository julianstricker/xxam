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
        dump($args);
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
        return $user->getMailaccounts();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        error_reporting(E_ALL);
        
        $time_start = microtime(true);
        
        
        $loop = \React\EventLoop\Factory::create();
        $dnsResolverFactory = new \React\Dns\Resolver\Factory();
        $dns = $dnsResolverFactory->createCached('8.8.8.8', $loop);
        $connector = new \React\SocketClient\Connector($loop, $dns);
        $secureConnector = new \React\SocketClient\SecureConnector($connector, $loop);
        
        $secureConnector->create('julianstricker.com', 993)->then(function (\React\Stream\Stream $stream) {
            $uid=uniqid();
            $login='julian@julianstricker.com';
            $password='llambda.3';
            $stream->write($uid." LOGIN $login $password\r\n");
            $status='LOGIN';
            $stream->on('data',function($data) use ($uid, &$status, &$stream){
                echo $data; 
                if (preg_match("/^".$uid." OK/", $data)){ //login OK:
                    if ($status=='LOGIN'){
                        $stream->write($uid." SELECT INBOX\r\n");
                        $status='SELECT';
                    }elseif ($status=='SELECT'){
                        $stream->write($uid." IDLE\r\n");
                        $status='IDLE';
                    }
                }
             });
            
        });
        

        $loop->run();
        
        
        
        
        
        
        
        
        
        
        
        die();
        
        $container = $this->getApplication()->getKernel()->getContainer();
        $this->em = $container->get('doctrine')->getManager('default');
        //Multiclient Filter:
        $this->tenant_id=$input->getArgument('tenant_id');
        $filter = $this->em->getFilters()->enable('tenant_filter');
        $filter->setParameter('tenant_id', $this->tenant_id);
        
        

        $client = new Client("realm1");
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
                    foreach($res->getResultMessage()->getArguments() as $chatuser){
                        $mailaccounts=$this->getMailaccountsForChatuser($chatuser);
                        foreach($mailaccounts as $mailaccount){
                            if ($mailaccount->getImapserver()!=''){
                                if (!isset($this->imapaccounts[$mailaccount->getId()])){
                                    $this->imapaccounts[$mailaccount->getId()]=Array(
                                        'mailaccount'=>$mailaccount,
                                        'users'=>Array($chatuser->session)
                                    );
                                }else{
                                    $this->imapaccounts[$mailaccount->getId()]['users'][]=$chatuser->session;
                                }
                            }
                        }
                    }
                },
                function ($error) {
                    echo "Call Error: \n";
                }
            );
        });
        $client->start();


        
        
        
        
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        $returntext="\n<info>OK in $time Sekunden.</info>";
        $output->writeln($returntext);
    }
    
    

}




?>
