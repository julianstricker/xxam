<?php

namespace Xxam\CommBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Doctrine\ORM\Query\ResultSetMapping;
use Xxam\CommBundle\Provider\XxamChat;
use Ratchet\WebSocket\WsServer;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;

class XxamchatCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('xxam:chat')
                ->setDescription('The Xxam chat server using ratchet')
                //->addArgument('tenant_id', InputArgument::REQUIRED, 1)
        
                
        ;
    }
    
    

    protected function execute(InputInterface $input, OutputInterface $output) {
        $time_start = microtime(true);
        $container = $this->getApplication()->getKernel()->getContainer();


        /*$server = new \Ratchet\App('127.0.0.1',1337);
        $server->route('/pubsub', new XxamPubSub);
        $server->route('/websocket', new \Ratchet\Server\EchoServer, array('*'));
        $server->run();
*/
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new XxamChat()
                )), 1337);

        $server->run();


        $time_end = microtime(true);
        $time = $time_end - $time_start;
        $returntext="\n<info>OK in $time Sekunden.</info>";
        $output->writeln($returntext);
    }
    
    

}




?>
