<?php

namespace Xxam\CommBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Doctrine\ORM\Query\ResultSetMapping;
use Thruway\Peer\Router;
use Xxam\CommBundle\Provider\XxamInternalClient;
use Xxam\CommBundle\Provider\ChattokenAuthenticationProvider;
use Thruway\Transport\RatchetTransportProvider;

class XxamchatCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('xxam:chat')
                ->setDescription('The Xxam chat server')
                //->addArgument('tenant_id', InputArgument::REQUIRED, 1)
        
                
        ;
    }
    
    

    protected function execute(InputInterface $input, OutputInterface $output) {
        $time_start = microtime(true);
        $container = $this->getApplication()->getKernel()->getContainer();
//        $em = $container->get('doctrine')->getManager('default');
//        //Multiclient Filter:
//        $tenant_id=$input->getArgument('tenant_id');
//        $filter = $em->getFilters()->enable('tenant_filter');
//        $filter->setParameter('tenant_id', $tenant_id);
        
        
        $router = new Router();
        $router->registerModule(new \Thruway\Authentication\AuthenticationManager());
        
        
        $transportProvider = new RatchetTransportProvider("localhost", 1337,$container);
        $router->addTransportProvider($transportProvider);
        $key = $container->getParameter('secret');
        $tenants=$container->getParameter('tenants');
        
        $realms=Array('realm0');
        foreach($tenants as $tenant){
            $realm='realm'.$tenant['id'];
            $realms[]=$realm;
            $router->addInternalClient(new XxamInternalClient($realm));
        }
        $router->addInternalClient(new ChattokenAuthenticationProvider($realms, $key)); 
        
        $router->start();
        
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        $returntext="\n<info>OK in $time Sekunden.</info>";
        $output->writeln($returntext);
    }
    
    

}




?>
