<?php

namespace Just\MailclientBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Just\MailclientBundle\Helper\Imap\ImapMailbox;
use Just\MailclientBundle\Entity\Mailaccount;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpFoundation\Session;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class MailclientExtraController extends MailclientBaseController {
    
    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_LIST')")
     *
     */
    public function getmailboxinfoAction(Request $request){
        $path=$request->get('path','');
        if ($path){
            $pathexpl=explode('.',$path);
            $mailaccountid=$pathexpl[0];
            unset($pathexpl[0]);
            $path=count($pathexpl)>0 ? '.'.implode('.',$pathexpl) : '';
        }
        $mailaccount=$this->getUserMailaccountForId($mailaccountid);
        if (!$mailaccount){
           return $this->throwJsonError('Mailaccount not found');
        }
        $mailbox = $this->getImapMailbox($mailaccount,$path);
        //$mailboxinfo=$mailbox->getMailboxInfo();
        $mailboxinfo=$mailbox->statusMailbox(); //=schneller als getMailboxInfo() aber ohne size;
        
        $response = new Response(json_encode(Array('mailboxinfo'=>$mailboxinfo)));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response; 
    }
    
    public function movemailsAction(Request $request){
        $from=$request->get('from','');
        $to=$request->get('to','');
        $ids=$request->get('ids','');
        
        
        if ($from){
            $pathexpl=explode('.',$from);
            $mailaccountid=$pathexpl[0];
            unset($pathexpl[0]);
            $from=count($pathexpl)>0 ? '.'.implode('.',$pathexpl) : '';
        }
        if ($to){
            $pathexpl=explode('.',$to);
           // $mailaccountid=$pathexpl[0];
            unset($pathexpl[0]);
            $to=count($pathexpl)>0 ? '.'.implode('.',$pathexpl) : '';
        }
        $mailaccount=$this->getUserMailaccountForId($mailaccountid);
        if (!$mailaccount){
           return $this->throwJsonError('Mailaccount not found');
        }
        
        //$frommailbox = new ImapMailbox($mailaccount->getConnectionstring().($from!='' ? $from : ''), $mailaccount->getUsername(), $mailaccount->getPassword(), $this->attachments_dir, 'UTF-8');
        $frommailbox = $this->getImapMailbox($mailaccount,$from);
        //$tomailbox = new ImapMailbox($mailaccount->getConnectionstring().($to!='' ? $to : ''), $mailaccount->getUsername(), $mailaccount->getPassword(), $this->attachments_dir, 'UTF-8');
        
       
        $idsarr=explode(',',$ids);
        $mailsbyid=Array();
        $movedids=Array();
        foreach($idsarr as $id){
            //echo "\nid: ".$id.' to: '.substr($mailaccount->getConnectionstring(),strpos($mailaccount->getConnectionstring(),'}')+1).$to.' ';
            if ($frommailbox->moveMail($id, $mailaccount->getImappathprefix().$to)){
                $movedids[]=$id;
            }
            
        }
        
        
        $response = new Response(json_encode(Array('status'=>'OK','ids'=>$movedids,'from'=>$request->get('from',''),'to'=>$request->get('to',''))));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response; 
    }
    
    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_DELETE')")
     *
     */
    public function trashmailsAction(Request $request){
        $from=$request->get('from','');
        $ids=$request->get('ids','');
        
        if ($from){
            $pathexpl=explode('.',$from);
            $mailaccountid=$pathexpl[0];
            unset($pathexpl[0]);
            $from=count($pathexpl)>0 ? '.'.implode('.',$pathexpl) : '';
        }
       
        
        $mailaccount=$this->getUserMailaccountForId($mailaccountid);
        if (!$mailaccount){
           return $this->throwJsonError('Mailaccount not found');
        }
        $to=$mailaccount->getTrashfolder();
        $returndata=Array();
        $totalcount=0;
        
        //$frommailbox = new ImapMailbox($mailaccount->getConnectionstring().($from!='' ? $from : ''), $mailaccount->getUsername(), $mailaccount->getPassword(), $this->attachments_dir, 'UTF-8');
        $frommailbox = $this->getImapMailbox($mailaccount,$from);
        //$tomailbox = new ImapMailbox($mailaccount->getConnectionstring().($to!='' ? $to : ''), $mailaccount->getUsername(), $mailaccount->getPassword(), $this->attachments_dir, 'UTF-8');
        $folders=$frommailbox->getListingFolders();
        $exists=false;
        if (!in_array($to,$folders)){
            //create 'Trash'-folder:
        }
        
        $idsarr=explode(',',$ids);
        $mailsbyid=Array();
        $movedids=Array();
        foreach($idsarr as $id){
            //echo "\nid: ".$id.' to: '.substr($mailaccount->getConnectionstring(),strpos($mailaccount->getConnectionstring(),'}')+1).$to.' ';
            if ($frommailbox->moveMail($id, $mailaccount->getImappathprefix().$to)){
                $movedids[]=$id;
            }
            
        }
        
        
        $response = new Response(json_encode(Array('status'=>'OK','ids'=>$movedids,'from'=>$request->get('from',''))));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response; 
    }
    
    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_DELETE')")
     *
     */
    public function junkmailsAction(Request $request){
        $from=$request->get('from','');
        $ids=$request->get('ids','');
        
        if ($from){
            $pathexpl=explode('.',$from);
            $mailaccountid=$pathexpl[0];
            unset($pathexpl[0]);
            $from=count($pathexpl)>0 ? '.'.implode('.',$pathexpl) : '';
        }
       
        
        $mailaccount=$this->getUserMailaccountForId($mailaccountid);
        if (!$mailaccount){
           return $this->throwJsonError('Mailaccount not found');
        }
        $to=$mailaccount->getJunkfolder();
        $returndata=Array();
        $totalcount=0;
        
        //$frommailbox = new ImapMailbox($mailaccount->getConnectionstring().($from!='' ? $from : ''), $mailaccount->getUsername(), $mailaccount->getPassword(), $this->attachments_dir, 'UTF-8');
        $frommailbox = $this->getImapMailbox($mailaccount,$from);
        //$tomailbox = new ImapMailbox($mailaccount->getConnectionstring().($to!='' ? $to : ''), $mailaccount->getUsername(), $mailaccount->getPassword(), $this->attachments_dir, 'UTF-8');
        $folders=$frommailbox->getListingFolders();
        $exists=false;
        if (!in_array($to,$folders)){
            //create 'Trash'-folder:
        }
        
        $idsarr=explode(',',$ids);
        $mailsbyid=Array();
        $movedids=Array();
        foreach($idsarr as $id){
            //echo "\nid: ".$id.' to: '.substr($mailaccount->getConnectionstring(),strpos($mailaccount->getConnectionstring(),'}')+1).$to.' ';
            if ($frommailbox->moveMail($id, $mailaccount->getImappathprefix().$to)){
                $movedids[]=$id;
            }
            
        }
        
        
        $response = new Response(json_encode(Array('status'=>'OK','ids'=>$movedids,'from'=>$request->get('from',''))));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response; 
    }
    
    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_EDIT')")
     *
     */
    public function markmailsasreadAction(Request $request){
        $from=$request->get('from','');
        $ids=$request->get('ids','');
        
        if ($from){
            $pathexpl=explode('.',$from);
            $mailaccountid=$pathexpl[0];
            unset($pathexpl[0]);
            $from=count($pathexpl)>0 ? '.'.implode('.',$pathexpl) : '';
        }
       
        
        $mailaccount=$this->getUserMailaccountForId($mailaccountid);
        if (!$mailaccount){
           return $this->throwJsonError('Mailaccount not found');
        }
        
        $returndata=Array();
        $totalcount=0;
        
        //$frommailbox = new ImapMailbox($mailaccount->getConnectionstring().($from!='' ? $from : ''), $mailaccount->getUsername(), $mailaccount->getPassword(), $this->attachments_dir, 'UTF-8');
        $frommailbox = $this->getImapMailbox($mailaccount,$from);
        //$tomailbox = new ImapMailbox($mailaccount->getConnectionstring().($to!='' ? $to : ''), $mailaccount->getUsername(), $mailaccount->getPassword(), $this->attachments_dir, 'UTF-8');
        
        
        $idsarr=explode(',',$ids);
        $mailsbyid=Array();
        $movedids=Array();
        foreach($idsarr as $id){
            //echo "\nid: ".$id.' to: '.substr($mailaccount->getConnectionstring(),strpos($mailaccount->getConnectionstring(),'}')+1).$to.' ';
            if ($frommailbox->markMailAsRead($id)){
                $movedids[]=$id;
            }
            
        }
        
        
        $response = new Response(json_encode(Array('status'=>'OK','ids'=>$movedids,'from'=>$request->get('from',''))));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response; 
    }
    
    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_EDIT')")
     *
     */
    public function markmailsasunreadAction(Request $request){
        $from=$request->get('from','');
        $ids=$request->get('ids','');
        
        if ($from){
            $pathexpl=explode('.',$from);
            $mailaccountid=$pathexpl[0];
            unset($pathexpl[0]);
            $from=count($pathexpl)>0 ? '.'.implode('.',$pathexpl) : '';
        }
       
        
        $mailaccount=$this->getUserMailaccountForId($mailaccountid);
        if (!$mailaccount){
           return $this->throwJsonError('Mailaccount not found');
        }
        
        $returndata=Array();
        $totalcount=0;
        
        //$frommailbox = new ImapMailbox($mailaccount->getConnectionstring().($from!='' ? $from : ''), $mailaccount->getUsername(), $mailaccount->getPassword(), $this->attachments_dir, 'UTF-8');
        $frommailbox = $this->getImapMailbox($mailaccount,$from);
        //$tomailbox = new ImapMailbox($mailaccount->getConnectionstring().($to!='' ? $to : ''), $mailaccount->getUsername(), $mailaccount->getPassword(), $this->attachments_dir, 'UTF-8');
        
        
        $idsarr=explode(',',$ids);
        $mailsbyid=Array();
        $movedids=Array();
        foreach($idsarr as $id){
            //echo "\nid: ".$id.' to: '.substr($mailaccount->getConnectionstring(),strpos($mailaccount->getConnectionstring(),'}')+1).$to.' ';
            if ($frommailbox->markMailAsUnread($id)){
                $movedids[]=$id;
            }
            
        }
        
        
        $response = new Response(json_encode(Array('status'=>'OK','ids'=>$movedids,'from'=>$request->get('from',''))));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response; 
    }
    
    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_EDIT')")
     *
     */
    public function markmailsasflaggedAction(Request $request){
        $from=$request->get('from','');
        $ids=$request->get('ids','');
        
        if ($from){
            $pathexpl=explode('.',$from);
            $mailaccountid=$pathexpl[0];
            unset($pathexpl[0]);
            $from=count($pathexpl)>0 ? '.'.implode('.',$pathexpl) : '';
        }
       
        
        $mailaccount=$this->getUserMailaccountForId($mailaccountid);
        if (!$mailaccount){
           return $this->throwJsonError('Mailaccount not found');
        }
        
        $returndata=Array();
        $totalcount=0;
        
        //$frommailbox = new ImapMailbox($mailaccount->getConnectionstring().($from!='' ? $from : ''), $mailaccount->getUsername(), $mailaccount->getPassword(), $this->attachments_dir, 'UTF-8');
        $frommailbox = $this->getImapMailbox($mailaccount,$from);
        //$tomailbox = new ImapMailbox($mailaccount->getConnectionstring().($to!='' ? $to : ''), $mailaccount->getUsername(), $mailaccount->getPassword(), $this->attachments_dir, 'UTF-8');
        
        
        $idsarr=explode(',',$ids);
        $mailsbyid=Array();
        $movedids=Array();
        if ($frommailbox->setFlag($idsarr, '\\Flagged')){
            $movedids=$idsarr;
        }
            
        
        
        
        $response = new Response(json_encode(Array('status'=>'OK','ids'=>$movedids,'from'=>$request->get('from',''))));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response; 
    }
    
    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_EDIT')")
     *
     */
    public function markmailsasunflaggedAction(Request $request){
        $from=$request->get('from','');
        $ids=$request->get('ids','');
        
        if ($from){
            $pathexpl=explode('.',$from);
            $mailaccountid=$pathexpl[0];
            unset($pathexpl[0]);
            $from=count($pathexpl)>0 ? '.'.implode('.',$pathexpl) : '';
        }
       
        
        $mailaccount=$this->getUserMailaccountForId($mailaccountid);
        if (!$mailaccount){
           return $this->throwJsonError('Mailaccount not found');
        }
        
        $returndata=Array();
        $totalcount=0;
        
        //$frommailbox = new ImapMailbox($mailaccount->getConnectionstring().($from!='' ? $from : ''), $mailaccount->getUsername(), $mailaccount->getPassword(), $this->attachments_dir, 'UTF-8');
        $frommailbox = $this->getImapMailbox($mailaccount,$from);
        //$tomailbox = new ImapMailbox($mailaccount->getConnectionstring().($to!='' ? $to : ''), $mailaccount->getUsername(), $mailaccount->getPassword(), $this->attachments_dir, 'UTF-8');
        
        
        $idsarr=explode(',',$ids);
        $mailsbyid=Array();
        $movedids=Array();
        
        if ($frommailbox->clearFlag($idsarr, '\\Flagged')){
            $movedids=$idsarr;
        }
        
        
        $response = new Response(json_encode(Array('status'=>'OK','ids'=>$movedids,'from'=>$request->get('from',''))));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response; 
    }
    
    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_CREATE')")
     *
     */
    public function sendmailAction(Request $request) {
        $fieldfrom=$request->get('fieldfrom',0);
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        $mailaccounts=$user->getMailaccounts();
        $mailaccount=false;
        foreach($mailaccounts as $ma){
            if ($fieldfrom==$ma->getId()){
                $mailaccount=$ma;
                break;
            }
        }
        if (!$mailaccount){ 
            return $this->throwJsonError('Mailaccount not found');
        }
        // switch to new settings
        $transport = \Swift_SmtpTransport::newInstance($mailaccount->getSmtpserver(), $mailaccount->getSmtpport() != '' ? $mailaccount->getSmtpport() : 25)->setUsername($mailaccount->getSmtpusername())->setPassword($mailaccount->getSmtppassword());
        if ($mailaccount->getSmtpsecurity()!=0) $transport->setEncryption($mailaccount->getSmtpsecurity()==1 || $mailaccount->getSmtpsecurity()==2 ? 'ssl' : 'tls');

        $mailer = \Swift_Mailer::newInstance($transport);
        $logger = new \Swift_Plugins_Loggers_ArrayLogger();
        $mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($logger));

        $message = \Swift_Message::newInstance()
                ->setSubject($request->get('fieldsubject',''))
                ->setFrom(array($mailaccount->getAccountemail()=>$mailaccount->getName()));
        try {
            if ($request->get('fieldreplyto',false)) $message->setReplyto($this->cleanEmailaddress($request->get('fieldreplyto',false)));
            //dump($this->cleanEmailaddress($request->get('fieldto',false)));
            if ($request->get('fieldto',false)) $message->setTo($this->cleanEmailaddress($request->get('fieldto',false)));
            if ($request->get('fieldbcc',false)) $message->setBcc($this->cleanEmailaddress($request->get('fieldbcc',false)));
            if ($request->get('fieldcc',false)) $message->setCc($this->cleanEmailaddress($request->get('fieldcc',false)));
        } catch (\Swift_RfcComplianceException $e) {
            // Catch exceptions of type Swift_TransportException        
            return $this->throwJsonError($e->getMessage());
        } catch (\Exception $e) {
            // Catch default PHP exceptions
            return $this->throwJsonError($e->getMessage());
        }
        if ($request->get('fieldhtml','') != ''){
            $fieldhtml=$request->get('fieldhtml','');
            $regex = '~data:[^;]+;[A-Za-z0-9]+,[^")\'\s]+~';
            preg_match_all($regex, $fieldhtml, $treffers, PREG_OFFSET_CAPTURE);
            $treffers=$treffers[0];
            foreach($treffers as $treffer){
                $trefferexpl=explode(',',$treffer[0],2);
                $data=explode(';',$trefferexpl[0]);
                $data=explode(':',$data[0]);
                $mimetype=$data[1];
                
                $decoded=base64_decode($trefferexpl[1]);
                $image=new \Swift_Image();
                $image->setBody($decoded,$mimetype);
                $cid=$message->embed($image);
                $fieldhtml=str_replace($treffer[0],$cid,$fieldhtml);
            }
            
            $message->setContentType("text/html")->setBody($request->get('fieldhtml',''), 'text/html');
            if ($request->get('fieldtext','') != '') $message->addPart($request->get('fieldtext',''), 'text/plain');
        }else{
            if ($request->get('fieldtext','') != '') $message->setContentType("text/plain")->setBody($request->get('fieldtext',''), 'text/plain');
        }
        //attachments:
        if ($request->get('fieldattachments','') != ''){
            $attachments=json_decode($request->get('fieldattachments',''));
            $session  = $this->get("session");
            $fileuploads=$session->get('mailclient_fileuploads',Array());
            if (count($attachments)>0){
                foreach($attachments as $attachment){
                    $file=$this->get('kernel')->getCacheDir() . '/mailclient_fileuploads/'.$user->getId().'/'.$attachment;
                    $filename=isset($fileuploads[$attachment]) ? $fileuploads[$attachment] : $attachment;
                    $message->attach(\Swift_Attachment::fromPath($file)->setFilename($filename));
                }
            }
        }
        
        try {
            $response = $mailer->send($message);
        } catch (\Swift_TransportException $e) {
            // Catch exceptions of type Swift_TransportException        
            return $this->throwJsonError($e->getMessage());
        } catch (\Exception $e) {
            // Catch default PHP exceptions
            dump($e->getCode());
            dump($e->getLine());
            dump($e->getTraceAsString());
            
            return $this->throwJsonError($e->getMessage());
        }
        
        if (!$response){
            return $this->throwJsonError($logger->dump());
        }
        //move into sent folder:
        $msg = $message->toString(); 
        //  (this creates the full MIME message required for imap_append()!!
        //  After this you can call imap_append like this:
        $folder=ltrim($mailaccount->getSentfolder(),'.');
        $mailbox = $this->getImapMailbox($mailaccount,$mailaccount->getSentfolder());
        $resp=$mailbox->addMail($msg,true);
        //$iares=imap_append($mailbox->getImapStream(),$folder,$msg."\r\n","\\Seen"); 
        $llogger = $this->get('logger');
        $llogger->info($msg);
        $llogger->info('RESPONSE : '.intval($iares));
        $llogger->info(imap_last_error());
        
        $response = new Response(json_encode(Array('status'=>'OK')));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response; 
        
        
        //return $this->render('JustMailclientBundle:Mailclient:write.js.twig', array('id'=>$id,'path'=>$path,'mailaccounts'=>$mas));
    }
    
    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_CREATE')")
     *
     */
    public function uploadfileAction(Request $request) {
        $filename=preg_replace("([^\w\s\d\-_~,;:\[\]\(\).])", '', $request->headers->get('X_FILENAME'));
        $file=file_get_contents('php://input');
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        if (!is_dir($this->get('kernel')->getCacheDir() . '/mailclient_fileuploads')) {
            mkdir($this->get('kernel')->getCacheDir() . '/mailclient_fileuploads');
        }
        if (!is_dir($this->get('kernel')->getCacheDir() . '/mailclient_fileuploads/'.$user->getId())) {
            mkdir($this->get('kernel')->getCacheDir() . '/mailclient_fileuploads/'.$user->getId());
        }
        $newfilename=md5($user->getId().microtime().rand(0,100000));
        $session  = $this->get("session");
        $fileuploads=$session->get('mailclient_fileuploads',Array());
        $fileuploads[$newfilename]=$filename;
        $session->set('mailclient_fileuploads',$fileuploads);
        
        //if (session_id()) session_write_close();
        $session->save();
        //$encodedData = str_replace(' ','+',substr($file,strpos($file,",")+1));
        //$decodedData = base64_decode($encodedData);
        
        file_put_contents(
            $this->get('kernel')->getCacheDir() . '/mailclient_fileuploads/'.$user->getId().'/'.$newfilename,
            $file
        );
        $response = new Response(json_encode(Array('status'=>'OK','filename'=>$filename,'hash'=>$newfilename)));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response; 
    }
    
}
