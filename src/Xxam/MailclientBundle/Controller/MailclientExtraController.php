<?php

namespace Xxam\MailclientBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Xxam\MailclientBundle\Entity\Mailspool;

class MailclientExtraController extends MailclientBaseController {
    
    /**
     * Mailclient getmailboxinfoAction
     *
     * @Security("has_role('ROLE_MAILCLIENT_LIST')")
     *
     * @param Request $request
     * @return Response
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

    /**
     * Mailclient movemailsAction
     *
     * @Security("has_role('ROLE_MAILCLIENT_DELETE')")
     *
     * @param Request $request
     * @return Response
     */
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
     * @param Request $request
     * @return Response
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
     * @param Request $request
     * @return Response
     */
    public function junkmailsAction(Request $request){
        $idsarr=explode(',',$request->get('ids',''));
        $frommailbox=$this->getImapMailboxForPath($request->get('from',''));
        if(!$frommailbox) $this->throwJsonError('Mailaccount not found');
        $mailaccountid=$this->getMailaccountidFromPath($request->get('from',''));
        $mailaccount=$this->getUserMailaccountForId($mailaccountid);
        if (!$mailaccount){
           return $this->throwJsonError('Mailaccount not found');
        }
        $to=$mailaccount->getJunkfolder();
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
     * @param Request $request
     * @return Response
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
     * @param Request $request
     * @return Response
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
     * @param Request $request
     * @return Response
     */
    public function markmailsasflaggedAction(Request $request){
        $from=$request->get('from','');
        $idsarr=explode(',',$request->get('ids',''));
        $frommailbox=$this->getImapMailboxForPath($from);
        if(!$frommailbox) $this->throwJsonError('Mailaccount not found');
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
     * @param Request $request
     * @return Response
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
     * @param Request $request
     * @return Response
     */
    public function sendmailAction(Request $request) {
        $fieldfrom=$request->get('fieldfrom',0);
        $mailaccount=$this->getUserMailaccountForId($fieldfrom);
        if (!$mailaccount){
            return $this->throwJsonError('Mailaccount not found');
        }
        //$mailer = $this->getMailerForMailaccount($mailaccount);
        $message=$this->generateMailForRequest($request);
        if (get_class($message)=='Symfony\Component\HttpFoundation\Response'){ //on Error:
            return $message;
        }

        /*$resp=$this->sendmail($mailer,$message);
        if ($resp!==true && get_class($resp)=='Symfony\Component\HttpFoundation\Response'){ //on Error:
            return $resp;
        }*/

        $spool=new Mailspool();
        $spool->mapMessage($message,$this->get('security.token_storage')->getToken()->getUser(),$mailaccount);
        $sendafter=$request->get('fieldsendafter');
        if (is_numeric($sendafter)){
            $sendafter = new \DateTime();
            $sendafter->add(new \DateInterval('PT'.$request->get('fieldsendafter').'M'));
        }else{
            $sendafter= new \DateTime($sendafter);
        }
        $spool->setSendafter($sendafter);
        $em = $this->getDoctrine()->getManager();
        $em->persist($spool);
        $em->flush();



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
     * @param Request $request
     * @return Response
     */
    public function uploadfileAction(Request $request) {

        $filename=preg_replace("([^\w\s\d\-_~,;:\[\]\(\).])", '', $request->headers->get('X-Filename'));

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
        $fileuploads[$newfilename]=['filename'=>$filename,'filepath'=>$this->get('kernel')->getCacheDir() . '/mailclient_fileuploads/'.$user->getId().'/'.$newfilename];
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

    /**
     * Mailclient imageproxyAction
     *
     * @Security("has_role('ROLE_MAILCLIENT_LIST')")
     *
     * @param Request $request
     * @return Response
     */
    public function imageproxyAction(Request $request){
        $url=$request->get('url','');
        $md5url=md5($url);
        $response = new Response();
        if ($request->server->has('HTTP_IF_NONE_MATCH') && trim($request->server->get('HTTP_IF_NONE_MATCH'))==$md5url){
            $response->setStatusCode(304,'Not Modified');
            return $response;
        }
        $memcached = $this->get('memcached');

        $imgfromcache=$memcached->get('xxam_mailclient_'.$md5url);

        if($imgfromcache){
            $response->headers->set('Content-Type',$imgfromcache['content_type']);
            //$response->headers->set('X-From-Cache','jou!');
            $response->setContent($imgfromcache['content']);
        }else{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $content_type = curl_getinfo($ch,CURLINFO_CONTENT_TYPE);
            $content=curl_exec($ch);
            curl_close($ch);
            $response->headers->set('Content-Type',$content_type);
            $response->setContent($content);
            $res=$memcached->set('xxam_mailclient_'.$md5url,['content_type'=>$content_type,'content'=>$content],time() + 86400);
            //$response->headers->set('X-From-Cache','Na!'.($res ? 'jo':'na').$memcached->getResultCode());
        }
        $response->setEtag($md5url);
        return $response;
    }
    
}
