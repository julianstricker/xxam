<?php

namespace Xxam\MailclientBundle\Command;

use Xxam\MailclientBundle\Entity\Mailaccount;
use Xxam\MailclientBundle\Entity\Mailspool;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Xxam\MailclientBundle\Helper\Imap\ImapMailbox;

class MailspoolCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('xxam:mailspool')
            ->setDescription('Email spooler')
            ->addArgument('id', InputArgument::OPTIONAL, 'The mailspool_id')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
        $mailspool_id = $input->getArgument('id');
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager('default');
        $em->getFilters()->disable('tenant_filter');
        $repository = $em->getRepository('XxamMailclientBundle:Mailspool');
        $query = $repository->createQueryBuilder('s');
        $timezone=new \DateTimeZone('UTC');
        $nowutc=new \DateTime('now');
        $nowutc->setTimezone($timezone);

        if ($mailspool_id){
            $query->where('s.id = :id');
            $query->setParameter('id', $mailspool_id);
        }else {

            $query->where('s.sendtime IS NULL AND s.sendafter <= :timenow AND s.sendstatus < 3');
            $query->setParameter('timenow', $nowutc);
        }
        /** @var mailspool[] $mailspools */
        $mailspools = $query->getQuery()->execute();
        $output->writeln('<info>Found ' . count($mailspools) . ' Mails to send...</info>' . "\n");
        foreach ($mailspools as $mailspool) {
            $output->writeln('<info>Id:</info> ' . $mailspool->getId() . "\n");
            $mailaccount=$mailspool->getMailaccount();
            $mailer = $this->getMailerForMailaccount($mailaccount);
            $logger = new \Swift_Plugins_Loggers_ArrayLogger();
            $mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($logger));

            /** @var \Swift_Message $message */
            $message = $mailspool->getMessage();

            $error = false;
            try {
                $mailer->send($message, $failures);
            } catch (\Swift_TransportException $e) {
                // Catch exceptions of type Swift_TransportException        
                $output->writeln('<error>Unable to send mail (swift): ' . date("Y-m-d H:i:s") . ' - ' . $e->getMessage() . '</error> ' . "\n");
                $error = true;
            } catch (\Exception $e) {
                // Catch default PHP exceptions
                $output->writeln('<error>Unable to send mail</error> ' . "\n");
                $error = true;
            }

            $output->writeln($logger->dump());
            $mailspool->setSendlog($logger->dump());
            $mailspool->setSendstatus($mailspool->getSendstatus()+1);

            if ($error == false) {
                $mailspool->setSendtime(new \DateTime('now'));
                //move into sent folder:
                $msg = $message->toString();
                //  (this creates the full MIME message required for imap_append()!!
                //  After this you can call imap_append like this:
                $folder=ltrim($mailaccount->getSentfolder(),'.');
                $mailbox = $this->getImapMailbox($mailaccount,$folder);
                $mailbox->addMail($msg,true);
            }else{
                $sendafter=new \DateTime($mailspool->getSendafter()->format('Y-m-d H:i:s'));
                if ($mailspool->getSendstatus()==1){ //try again in 5 minutes:
                    $sendafter->add(new \DateInterval('PT5M'));
                }else if ($mailspool->getSendstatus()==2){ //try again in 30 minutes:
                    $sendafter->add(new \DateInterval('PT30M'));
                }
                $mailspool->setSendafter($sendafter);
            }
            $em->persist($mailspool);
            $em->flush($mailspool);
        }


        $returntext = ""; //"\n<error>ok</error>";

        $output->writeln($returntext);
    }

    protected function getImapMailbox(Mailaccount $mailaccount, $path='')
    {
        $securitystring='/imap';
        //0=off, 1=ssl/tls, 2=ssl/tls alle zertifikate akzeptieren, 3=starttls, 4 starttls alle zertifikate akzeptieren
        if ($mailaccount->getImapsecurity()==1){
            $securitystring='/imap/ssl';
        }else if ($mailaccount->getImapsecurity()==2){
            $securitystring='/imap/ssl/novalidate-cert';
        }else if ($mailaccount->getImapsecurity()==3){
            $securitystring='/imap/tls';
        }else if ($mailaccount->getImapsecurity()==4){
            $securitystring='/imap/tls/novalidate-cert';
        }
        $connectionstring="{".$mailaccount->getImapserver().":".($mailaccount->getImapport() ? $mailaccount->getImapport() : 143).$securitystring."}".$mailaccount->getImappathprefix().'.'.$path;
        $attachments_dir = realpath($this->getContainer()->get('kernel')->getRootDir() . '/../web/uploads/attachments/'.$mailaccount->getId());
        if (!is_dir($attachments_dir)){
            mkdir($attachments_dir);
        }
        return new ImapMailbox($connectionstring, $mailaccount->getImapusername(), $mailaccount->getImappassword(), $attachments_dir, 'UTF-8');
    }

    /*
     * Create Swiftmailer-Class for Mailaccount
     */
    protected function getMailerForMailaccount(Mailaccount $mailaccount){
        // switch to new settings
        $transport = \Swift_SmtpTransport::newInstance($mailaccount->getSmtpserver(), $mailaccount->getSmtpport() != '' ? $mailaccount->getSmtpport() : 25)->setUsername($mailaccount->getSmtpusername())->setPassword($mailaccount->getSmtppassword());
        if ($mailaccount->getSmtpsecurity()!=0) $transport->setEncryption($mailaccount->getSmtpsecurity()==1 || $mailaccount->getSmtpsecurity()==2 ? 'ssl' : 'tls');

        $mailer = \Swift_Mailer::newInstance($transport);

        return $mailer;
    }

}

?>
