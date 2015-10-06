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
        $mailaccountid=$this->getMailaccountidFromPath($request->get('path',''));
        $path=$this->removeMailaccountidFromPath($request->get('path',''));
        $mailaccount=$this->getUserMailaccountForId($mailaccountid);
        if (!$mailaccount){
           return $this->throwJsonError('Mailaccount not found');
        }
        $mailbox = $this->getImapMailbox($mailaccount,$path);
        //$mailboxinfo=$mailbox->getMailboxInfo();
        $mailboxinfo=$mailbox->statusMailbox(); //=schneller als getMailboxInfo() aber ohne size;

        return $this->getJsonResponse(Array('mailboxinfo'=>$mailboxinfo));
    }
    
    public function movemailsAction(Request $request){
        $ids=$request->get('ids','');

        $mailaccountid=$this->getMailaccountidFromPath($request->get('from',''));
        $from=$this->removeMailaccountidFromPath($request->get('from',''));

        $to=$this->removeMailaccountidFromPath($request->get('to',''));

        $mailaccount=$this->getUserMailaccountForId($mailaccountid);
        if (!$mailaccount){
           return $this->throwJsonError('Mailaccount not found');
        }
        $frommailbox = $this->getImapMailbox($mailaccount,$from);

        $idsarr=explode(',',$ids);
        $movedids=Array();
        foreach($idsarr as $id){
            if ($frommailbox->moveMail($id, $mailaccount->getImappathprefix().$to)){
                $movedids[]=$id;
            }
            
        }
        return $this->getJsonResponse(Array('status'=>'OK','ids'=>$movedids,'from'=>$request->get('from',''),'to'=>$request->get('to','')));
    }
    
    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_DELETE')")
     *
     */
    public function trashmailsAction(Request $request){
        $mailaccountid=$this->getMailaccountidFromPath($request->get('from',''));
        $idsarr=explode(',',$request->get('ids',''));
        $frommailbox=$this->getImapMailboxForPath($request->get('from',''));
        if(!$frommailbox) $this->throwJsonError('Mailaccount not found');
        $mailaccount=$this->getUserMailaccountForId($mailaccountid);
        if (!$mailaccount){
            return $this->throwJsonError('Mailaccount not found');
        }
        $to=$mailaccount->getTrashfolder();
        $folders=$frommailbox->getListingFolders();
        if (!in_array($to,$folders)){
            //create 'Trash'-folder:
        }
        $movedids=Array();
        foreach($idsarr as $id){
            if ($frommailbox->moveMail($id, $mailaccount->getImappathprefix().$to)){
                $movedids[]=$id;
            }
        }
        return $this->getJsonResponse(Array('status'=>'OK','ids'=>$movedids,'from'=>$request->get('from','')));
    }
    
    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_DELETE')")
     *
     */
    public function junkmailsAction(Request $request){
        $from=$request->get('from','');
        $idsarr=explode(',',$request->get('ids',''));
        $frommailbox=$this->getImapMailboxForPath($from);
        if(!$frommailbox) $this->throwJsonError('Mailaccount not found');

        if ($from){
            $pathexpl=explode('.',$from);
            $mailaccountid=$pathexpl[0];
        }
        $mailaccount=$this->getUserMailaccountForId($mailaccountid);
        if (!$mailaccount){
           return $this->throwJsonError('Mailaccount not found');
        }
        $to=$mailaccount->getJunkfolder();

        $folders=$frommailbox->getListingFolders();
        if (!in_array($to,$folders)){
            //create 'Trash'-folder:
        }
        $movedids=Array();
        foreach($idsarr as $id){
            if ($frommailbox->moveMail($id, $mailaccount->getImappathprefix().$to)){
                $movedids[]=$id;
            }
        }
        return $this->getJsonResponse(Array('status'=>'OK','ids'=>$movedids,'from'=>$request->get('from','')));
    }


    
    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_EDIT')")
     *
     */
    public function markmailsasreadAction(Request $request){
        $from=$request->get('from','');
        $idsarr=explode(',',$request->get('ids',''));
        $frommailbox=$this->getImapMailboxForPath($from);
        if(!$frommailbox) $this->throwJsonError('Mailaccount not found');
        $movedids=Array();
        foreach($idsarr as $id){
            if ($frommailbox->markMailAsRead($id)){
                $movedids[]=$id;
            }
        }
        return $this->getJsonResponse(Array('status'=>'OK','ids'=>$movedids,'from'=>$request->get('from','')));
    }
    
    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_EDIT')")
     *
     */
    public function markmailsasunreadAction(Request $request){
        $from=$request->get('from','');
        $idsarr=explode(',',$request->get('ids',''));

        $frommailbox=$this->getImapMailboxForPath($from);
        if(!$frommailbox) $this->throwJsonError('Mailaccount not found');
        

        $movedids=Array();
        foreach($idsarr as $id){
            if ($frommailbox->markMailAsUnread($id)){
                $movedids[]=$id;
            }
        }
        return $this->getJsonResponse(Array('status'=>'OK','ids'=>$movedids,'from'=>$request->get('from','')));
    }
    
    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_EDIT')")
     *
     */
    public function markmailsasflaggedAction(Request $request){
        $from=$request->get('from','');
        $idsarr=explode(',',$request->get('ids',''));
        $frommailbox=$this->getImapMailboxForPath($from);
        if(!$frommailbox) $this->throwJsonError('Mailaccount not found');
        $mailsbyid=Array();
        $movedids=Array();
        if ($frommailbox->setFlag($idsarr, '\\Flagged')){
            $movedids=$idsarr;
        }
        return $this->getJsonResponse(Array('status'=>'OK','ids'=>$movedids,'from'=>$request->get('from','')));
    }
    
    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_EDIT')")
     *
     */
    public function markmailsasunflaggedAction(Request $request){
        $from=$request->get('from','');
        $idsarr=explode(',',$request->get('ids',''));
        $frommailbox=$this->getImapMailboxForPath($from);
        if(!$frommailbox) $this->throwJsonError('Mailaccount not found');
        $movedids=Array();
        if ($frommailbox->clearFlag($idsarr, '\\Flagged')){
            $movedids=$idsarr;
        }
        return $this->getJsonResponse(Array('status'=>'OK','ids'=>$movedids,'from'=>$request->get('from','')));
    }
    
    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_CREATE')")
     *
     */
    public function sendmailAction(Request $request) {
        $fieldfrom=$request->get('fieldfrom',0);
        $mailaccount=$this->getUserMailaccountForId($fieldfrom);
        if (!$mailaccount){
            return $this->throwJsonError('Mailaccount not found');
        }
        $mailer = $this->getMailerForMailaccount($mailaccount);
        $message=$this->generateMailForRequest($request);
        if (get_class($message)=='Response'){
            return $message;
        }

        try {
            $response = $mailer->send($message);
        } catch (\Swift_TransportException $e) {
            // Catch exceptions of type Swift_TransportException        
            return $this->throwJsonError($e->getMessage());
        } catch (\Exception $e) {
            return $this->throwJsonError($e->getMessage());
        }
        if (!$response){
            return $this->throwJsonError('Error sending mail');
        }
        //move into sent folder:
        $msg = $message->toString(); 
        //  (this creates the full MIME message required for imap_append()!!
        //  After this you can call imap_append like this:
        $folder=ltrim($mailaccount->getSentfolder(),'.');
        $mailbox = $this->getImapMailbox($mailaccount,$folder);
        $mailbox->addMail($msg,true);
        return $this->getJsonResponse(Array('status'=>'OK'));
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
        $user = $this->get('security.token_storage')->getToken()->getUser();
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
        return $this->getJsonResponse(Array('status'=>'OK','filename'=>$filename,'hash'=>$newfilename));
    }
    
}
