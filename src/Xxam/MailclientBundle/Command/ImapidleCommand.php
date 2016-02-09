<?php

namespace Xxam\MailclientBundle\Command;


use Doctrine\ORM\EntityManager;
use React\EventLoop\Factory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Xxam\MailclientBundle\Helper\Client;
use Xxam\CommBundle\Helper\WebSocketClient\WebSocketClient;



class ImapidleCommand extends ContainerAwareCommand {

    /* @var EntityManager $em */
    private $em;

    private $tenant_id;


    protected function configure() {
        $this
                ->setName('xxam:imapidle')
                ->setDescription('Connects to Imap Servers and notifies on new emails')
                ->addArgument('tenant_id', InputArgument::REQUIRED, 1)
        

        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output) {
        error_reporting(E_ALL);
        
        $time_start = microtime(true);

        $container = $this->getContainer(); //$this->getApplication()->getKernel()->getContainer();

        $this->em = $container->get('doctrine')->getManager('default');
        //Multiclient Filter:
        $this->tenant_id=$input->getArgument('tenant_id');
        $filter = $this->em->getFilters()->enable('tenant_filter');
        $filter->setParameter('tenant_id', $this->tenant_id);


        $loop = Factory::create();
        //WebSocketClientInterface $client, LoopInterface $loop, $host = '127.0.0.1', $port = 8080, $path = '/', $origin = null
        $client = new WebSocketClient(new Client( $this->tenant_id,$this->em,$loop), $loop, '127.0.0.1',1337);
        if ($client) echo 'loop run...';
        $loop->run();

        
        
        
        
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        $returntext="\n<info>OK in $time Sekunden.</info>";
        $output->writeln($returntext);
    }
    
    

}





?>
