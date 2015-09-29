<?php

namespace Just\MailclientBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Just\MailclientBundle\Helper\Imap\ImapMailbox;
use Just\MailclientBundle\Entity\Mailaccount;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpFoundation\Session;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class MailclientController extends MailclientBaseController {
    
    
    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_LIST')")
     *
     */
    public function indexAction() {
        $fileextensionswiththumbnails=$this->container->getParameter('fileextensionswiththumbnails');
        $fileextensiontomimetype=$this->container->getParameter('fileextensiontomimetype');
        return $this->render('JustMailclientBundle:Mailclient:index.js.twig', array(
            'fileextensionswiththumbnails'=>$fileextensionswiththumbnails,
            'fileextensiontomimetype'=>$fileextensiontomimetype
        ));
    }
    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_LIST')")
     *
     */
    public function showAction(Request $request) {
        $path=$request->get('path','');
        $id=$request->get('id','');
        $fileextensionswiththumbnails=$this->container->getParameter('fileextensionswiththumbnails');
        $fileextensiontomimetype=$this->container->getParameter('fileextensiontomimetype');
        
        return $this->render('JustMailclientBundle:Mailclient:show.js.twig', array(
            'id'=>$id,
            'path'=>$path,
            'fileextensionswiththumbnails'=>$fileextensionswiththumbnails,
            'fileextensiontomimetype'=>$fileextensiontomimetype
        ));
    }
    
    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_LIST')")
     *
     */
    public function listfoldersAction() {
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        $mailaccounts=$user->getMailaccounts();
        $returndata=Array();
        foreach($mailaccounts as $mailaccount){
            $mailbox = $this->getImapMailbox($mailaccount,'');
            $folders = $mailbox->getListingFolders();
            $children = Array();
            $mailaccountid=$mailaccount->getId();
            foreach ($folders as $folder) {
                $folderexpl = explode('.', trim($folder, '.'));
                $subfolder = &$children;
                $path=$mailaccountid;
                foreach ($folderexpl as $fexp) {
                    $path.='.'.$fexp;
                    if (!isset($subfolder['children'])) $subfolder['children']=Array();
                    if (!isset($subfolder['children'][$fexp])) {
                        $subfolder['children'][$fexp] = Array(
                            'text' => $fexp,
                            'path' => $path,
                            'loaded'=>true,
                            'expanded'=>true,
                            'leaf' => false,
                            'allowChildren' => true,
                        );
                        $subfolder['leaf']=false;
                        $subfolder['loaded']=false;
                        $subfolder['expanded']=false;
                        if ($mailaccountid.$mailaccount->getTrashfolder()==$path) $subfolder['children'][$fexp]['icon']='/bundles/justmailclient/icons/16x16/bin.png';
                        if ($mailaccountid.$mailaccount->getJunkfolder()==$path) $subfolder['children'][$fexp]['icon']='/bundles/justmailclient/icons/16x16/spam_assassin.png';
                        if ($mailaccountid.$mailaccount->getSentfolder()==$path) $subfolder['children'][$fexp]['icon']='/bundles/justmailclient/icons/16x16/email_go.png';
                        if ($mailaccountid.$mailaccount->getDraftfolder()==$path) $subfolder['children'][$fexp]['icon']='/bundles/justmailclient/icons/16x16/email_edit.png';
                    }
                    $subfolder = &$subfolder['children'][$fexp];
                }
            }
            $children['expanded']=true;
            $children['text']= $mailaccount->getAccountname();
            $children['path']=$mailaccountid;  //<-account_id


            $returndata[] =  $this->removeChildrenkeys($children);
        }
        //dump($returndata);
        //setLocale(LC_ALL,'de_DE.UTF8');
        $response = new Response(json_encode($returndata));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }
    
    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_LIST')")
     *
     */
    public function listmailsAction(Request $request){
        //$page=$request->get('page',1);
        $start=$request->get('start',0);
        $limit=$request->get('limit',100);
        $sort=$request->get('sort','arrival');
        $dir=$request->get('dir','DESC');
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
        $returndata=Array();
        
        $mailbox = $this->getImapMailbox($mailaccount,$path);
        $searchcriteria='ALL'; //'ALL';
        $sortarray=Array(
            'date' => SORTDATE,
            'arrival' => SORTARRIVAL,
	    'from' => SORTFROM,
            'subject'=> SORTSUBJECT,
            'to' => SORTTO,
            'cc' => SORTCC,
            'size' =>  SORTSIZE,
        );
        $mails_ids = $mailbox->sortMails(isset($sortarray[$sort]) ? $sortarray[$sort] : SORTARRIVAL, $dir=='DESC', $searchcriteria); 
        $totalcount=count($mails_ids);
        $pagingmails_ids=array_slice($mails_ids,$start,$limit);
        $mails=$mailbox->getMailsInfo($pagingmails_ids);
        $mailsbyid=Array();
        foreach($mails as $m){
            $mailsbyid[$m->uid]=$m;
        }
        //dump($mailsbyid);
        foreach($pagingmails_ids as $id){
            $returndata[]=$mailsbyid[$id];
        } 
        
        $response = new Response(json_encode(Array('totalCount'=>$totalcount,'mails'=>$returndata)));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response; 
    }
    
    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_LIST')")
     *
     */
    public function getmailAction(Request $request){
        $mailid=$request->get('mailid',false);
        $path=$request->get('path','');
        if ($path){
            $pathexpl=explode('.',$path);
            $mailaccountid=$pathexpl[0];
            unset($pathexpl[0]);
            $path=count($pathexpl)>0 ? '.'.implode('.',$pathexpl) : '';
        }
        if ($mailaccountid===false){
            return $this->throwJsonError('Please provide a mailacocuntid');
        }
        //die($path);
        if ($mailid===false){
            return $this->throwJsonError('Please provide a mailid');
        }
        $mailaccount=$this->getUserMailaccountForId($mailaccountid);
        if (!$mailaccount){
           return $this->throwJsonError('Mailaccount not found');
        }
        
        $mailbox = $this->getImapMailbox($mailaccount,$path);
        $mail = $mailbox->getMail($mailid); 
        $mail->hasexternallinks=false;
        if ($mail->textHtml){
            //clean html:
            $fetchedHtml=$this->cleanHtml($mail,false);
            if(strpos($fetchedHtml,$this->imageplaceholder)!==false){
                $mail->hasexternallinks=true;
            }
        }
        unset($mail->textHtml);
        unset($mail->textPlain);
        $attachments=Array();
        foreach($mail->getAttachments() as $attachment){
            $attachments[]=Array(
                'id'=>$attachment->id,
                'name'=>$attachment->name,
                'filesize'=>$attachment->fileSize,
                'filepath'=>str_replace($this->attachments_dir,$this->attachmentsbase_dir,$attachment->filePath)
                
            );
        }
        $mail->files=$attachments;
        $returndata=$mail;
//        $fetchedHtml=$mail->textHtml;
//        foreach($mail->getInternalLinksPlaceholders() as $attachmentId => $placeholder) {
//            if(isset($mail->attachments[$attachmentId]))
//                $fetchedHtml = str_replace($placeholder, $baseUri . basename($mail->attachments[$attachmentId]->filePath), $fetchedHtml);
//        }
        /*foreach($mail->getAttachments() as $attachment) {
            dump($attachment->filePath);
        }
        dump($mail->getInternalLinksPlaceholders());
        dump($fetchedHtml);*/
        //dump(json_encode($returndata->textHtml));
        $response = new Response(json_encode($returndata,JSON_HEX_QUOT));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response; 
    }
    
    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_LIST')")
     *
     */
    public function getmailcontentAction(Request $request){
        $path=$request->get('path','');
        $externalsources=$request->get('externalsources',false);
        if ($path){
            $pathexpl=explode('.',$path);
            $mailaccountid=$pathexpl[0];
            unset($pathexpl[0]);
            $path=count($pathexpl)>0 ? '.'.implode('.',$pathexpl) : '';
        }
        $mailid=$request->get('mailid',false);
        if ($mailaccountid===false){
            return $this->throwJsonError('Please provide a mailaccountid');
        }
        if ($mailid===false){
            return $this->throwJsonError('Please provide a mailid');
        }
        $mailaccount=$this->getUserMailaccountForId($mailaccountid);
        if (!$mailaccount){
           return $this->throwJsonError('Mailaccount not found');
        }
        $mailbox = $this->getImapMailbox($mailaccount,$path);
        $mail = $mailbox->getMail($mailid); 
        if ($mail->textHtml){
            //clean html:
            $fetchedHtml=$this->cleanHtml($mail,$externalsources);
            $response = new Response($fetchedHtml);
            $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
            return $response;
        }else{
            return $this->render('JustMailclientBundle:Mailclient:mailbody.html.twig', array('mailcontent' => '<pre>'.$mail->textPlain.'</pre>'));
        }
        
    }
    
    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_CREATE')")
     *
     */
    public function writeAction(Request $request) {
        $path=$request->get('path','');
        $mailid=$request->get('mailid','');
        $type=$request->get('type','');
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        $params=array(
            'type'=>$type,
            'mailid'=>$mailid,
            'path'=>$path,
            
        );
        if (in_array($type,array('reply','replyall','forward'))){
           if ($path){
                $pathexpl=explode('.',$path);
                $mailaccountid=$pathexpl[0];
                unset($pathexpl[0]);
                $path=count($pathexpl)>0 ? '.'.implode('.',$pathexpl) : '';
            }
            if ($mailaccountid===false){
                return $this->throwJsonError('Please provide a mailacocuntid');
            }
            if ($mailid===false){
                return $this->throwJsonError('Please provide a mailid');
            }
            $mailaccount=$this->getUserMailaccountForId($mailaccountid);
            if (!$mailaccount){
               return $this->throwJsonError('Mailaccount not found');
            }

            $mailbox = $this->getImapMailbox($mailaccount,$path);
            $mail = $mailbox->getMail($mailid);  
            
            $mail->hasexternallinks=false;
            if ($mail->textHtml){
                //clean html:
                $fetchedHtml=$this->cleanHtml($mail,false,'On.......wrote:');
                if(strpos($fetchedHtml,$this->imageplaceholder)!==false){
                    $mail->hasexternallinks=true;
                }
            }
            //unset($mail->textHtml);
            //unset($mail->textPlain);
            $mail->textHtml=$fetchedHtml;
            $attachments=Array();
            foreach($mail->getAttachments() as $attachment){
                $attachments[]=Array(
                    'id'=>$attachment->id,
                    'name'=>$attachment->name,
                    'filesize'=>$attachment->fileSize,
                    'filepath'=>str_replace($this->attachments_dir,$this->attachmentsbase_dir,$attachment->filePath)

                );
            }
            $mail->files=$attachments;
            $params['mail']=$mail;
        }
        if ($type=='reply' || $type=='replyall'){
            $params['mail']->subject='Re: '.$params['mail']->subject;
            $from=$params['mail']->to;
            $to = property_exists($params['mail'], 'fromAddress') ? Array($params['mail']->fromAddress => (property_exists($params['mail'], 'fromName') ? $params['mail']->fromName : $params['mail']->fromAddress) ) : '';
            if ( property_exists($params['mail'], 'replyTo') ){
                $to=$params['mail']->replyTo;
            }
            $params['mail']->from=$from;
            $params['mail']->to=$to;
            unset($params['mail']->fromName);
            unset($params['mail']->fromAddress);
            unset($params['mail']->toString);
            unset($params['mail']->replyTo);
            if ($type=='reply'){
                $params['mail']->cc=Array();
            }
        }
        
        //dump($params);
        $mailaccounts=$user->getMailaccounts();
        foreach($mailaccounts as $ma){
            $params['mailaccounts'][]=$ma;
        }
        return $this->render('JustMailclientBundle:Mailclient:write.js.twig', $params);
    }
    
    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_SETTINGS')")
     *
     */
    public function settingsAction(Request $request) {
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        $mailaccounts=$user->getMailaccounts();
        $mas=Array();
        foreach($mailaccounts as $mailaccount){
            $mas[]=$mailaccount;
        }
        return $this->render('JustMailclientBundle:Mailclient:settings.js.twig', array('mailaccounts'=>$mas));
    }
    
}
