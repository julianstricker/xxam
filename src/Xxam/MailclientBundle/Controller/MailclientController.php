<?php

namespace Xxam\MailclientBundle\Controller;


use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Xxam\MailclientBundle\Entity\Mailaccountuser;
use Xxam\MailclientBundle\Entity\MailaccountuserRepository;
use Xxam\MailclientBundle\Entity\Mailspool;
use Xxam\MailclientBundle\Helper\Imap\IncomingMail;

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
        return $this->render('XxamMailclientBundle:Mailclient:index.js.twig', array(
            'fileextensionswiththumbnails'=>$fileextensionswiththumbnails,
            'fileextensiontomimetype'=>$fileextensiontomimetype
        ));
    }
    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_LIST')")
     *
     * @param Request $request
     * @return Response A Response instance
     *
     */
    public function showAction(Request $request) {
        $path=$request->get('path','');
        $id=$request->get('id','');
        $fileextensionswiththumbnails=$this->container->getParameter('fileextensionswiththumbnails');
        $fileextensiontomimetype=$this->container->getParameter('fileextensiontomimetype');
        
        return $this->render('XxamMailclientBundle:Mailclient:show.js.twig', array(
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
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamMailclientBundle:Mailaccountuser');
        /* @var MailaccountuserRepository $repository */
        $mailaccountusers=$repository->findByUserId($user->getId());
        $returndata=Array();
        foreach($mailaccountusers as $mailaccountuser){
            /* @var Mailaccountuser $mailaccountuser */
            $mailaccount=$mailaccountuser->getMailaccount();
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
                        /*if ($mailaccountid.$mailaccount->getTrashfolder()==$path) $subfolder['children'][$fexp]['icon']='/bundles/xxammailclient/icons/16x16/bin.png';
                        if ($mailaccountid.$mailaccount->getJunkfolder()==$path) $subfolder['children'][$fexp]['icon']='/bundles/xxammailclient/icons/16x16/spam_assassin.png';
                        if ($mailaccountid.$mailaccount->getSentfolder()==$path) $subfolder['children'][$fexp]['icon']='/bundles/xxammailclient/icons/16x16/email_go.png';
                        if ($mailaccountid.$mailaccount->getDraftfolder()==$path) $subfolder['children'][$fexp]['icon']='/bundles/xxammailclient/icons/16x16/email_edit.png';*/

                        if ($mailaccountid.$mailaccount->getTrashfolder()==$path) $subfolder['children'][$fexp]['iconCls']='fa fa-recycle';
                        if ($mailaccountid.$mailaccount->getJunkfolder()==$path) $subfolder['children'][$fexp]['iconCls']='fa fa-trash-o';
                        if ($mailaccountid.$mailaccount->getSentfolder()==$path) $subfolder['children'][$fexp]['iconCls']='fa fa-send-o';
                        if ($mailaccountid.$mailaccount->getDraftfolder()==$path) $subfolder['children'][$fexp]['iconCls']='fa fa-pencil-square-o';
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
     * @param Request $request
     * @return Response
     *
     */
    public function listmailsAction(Request $request){
        //$page=$request->get('page',1);
        $start=$request->get('start',0);
        $limit=$request->get('limit',100);
        $sort=$request->get('sort','arrival');
        $dir=$request->get('dir','DESC');
        $filter=json_decode($request->get('filter','[]')); //[{"operator":"like","value":"test","property":"subject"},{"operator":"lt","value":"08/03/2016","property":"date"}]
        $path=$request->get('path','');
        $mailaccountid=0;
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
        if (count($filter)>0){
            $filterstrings=[];
            foreach($filter as $f){
                switch($f->property){
                    case 'subject':
                        $filterstrings[]='SUBJECT "'.$f->value.'"';
                        break;
                    case 'from':
                        $filterstrings[]='FROM "'.$f->value.'"';
                        break;
                    case 'to':
                        $filterstrings[]='TO "'.$f->value.'"';
                        break;
                    case 'date':
                        $valuedateobj=new \DateTime($f->value);
                        $valuedate=$valuedateobj->format('d-M-Y');
                        if($f->operator=='lt'){
                            $filterstrings[]='BEFORE "'.$valuedate.'"';
                        }else if($f->operator=='gt'){
                            $filterstrings[]='SINCE "'.$valuedate.'"';
                        }else if ($f->operator=='eq'){
                            $filterstrings[]='ON "'.$valuedate.'"';
                        }
                        break;
                    case 'recent':
                        $filterstrings[]= $f->value ? 'NEW' : 'OLD';
                        break;
                    case 'flagged':
                        $filterstrings[]= $f->value ? 'FLAGGED' : 'UNFLAGGED';
                        break;
                    case 'answered':
                        $filterstrings[]= $f->value ? 'ANSWERED' : 'UNANSWERED';
                        break;
                    case 'deleted':
                        $filterstrings[]= $f->value ? 'DELETED' : 'UNDELETED';
                        break;
                    case 'seen':
                        $filterstrings[]= $f->value ? 'SEEN' : 'UNSEEN';
                        break;
                }
            }
            $searchcriteria=implode(' ',$filterstrings);
        }else{
            $searchcriteria='ALL'; //'ALL';
        }

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
        $timezone=new \DateTimeZone($request->getSession()->get('timezone'));

        foreach($mails as $m){
            if (property_exists($m,'date')){
                $date = new \DateTime($m->date);
                if ($date) $m->date = $date->setTimezone($timezone)->format('Y-m-d H:i:s');
            }
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
     * @param Request $request
     * @return Response
     */
    public function getmailAction(Request $request){
        $mailid=$request->get('mailid',false);
        $path=$request->get('path','');
        $mailaccountid=false;
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
            $fetchedHtml=$this->cleanHtml($mail->textHtml,false,$mail->getAttachments());
            if(strpos($fetchedHtml,$this->imageplaceholder)!==false){
                $mail->hasexternallinks=true;
            }
        }
        unset($mail->textHtml);
        unset($mail->textPlain);
        $attachments=Array();
        foreach($mail->getAttachments() as $attachment){
            if (property_exists($attachment,'disposition') && $attachment->disposition=='attachment') {
                $attachments[] = Array(
                    'id' => $attachment->id,
                    'name' => $attachment->name,
                    'filesize' => 0, //$attachment->fileSize,
                    'filepath' => str_replace($this->attachments_dir, $this->attachmentsbase_dir, $attachment->filePath)

                );
            }
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
     * @param Request $request
     * @return Response
     */
    public function getmailcontentAction(Request $request){

        $path=$request->get('path','');
        $externalsources=$request->get('externalsources',false);
        $mailaccountid=false;
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
            $fetchedHtml=$this->cleanHtml($mail->textHtml,$externalsources,$mail->getAttachments());
            $response = new Response($fetchedHtml);
            $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
            return $response;
        }else if ($mail->textPlain){
            return $this->render('XxamMailclientBundle:Mailclient:mailbody.html.twig', array('mailcontent' => '<pre>'.$mail->textPlain.'</pre>'));
        }else{

            $attachments=$mail->getAttachments();
            if(isset($attachments['html-body'])){
                $content=file_get_contents($attachments['html-body']->filePath);
                $fetchedHtml=$this->cleanHtml($content,$externalsources,$mail->getAttachments());
                $response = new Response($fetchedHtml);
                $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
                return $response;
            }else if (isset($attachments['text-body'])){
                $content=file_get_contents($attachments['text-body']->filePath);
                if (!$content) $content='';
                return $this->render('XxamMailclientBundle:Mailclient:mailbody.html.twig', array('mailcontent' => '<pre>'.$content.'</pre>'));
            }
            $response = new Response('');
            $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
            return $response;
        }
        
    }

    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_CREATE')")
     * @param Request $request
     * @return Response
     */
    public function writeAction(Request $request) {
        $path=$request->get('path','');
        $mailid=$request->get('mailid','');
        $type=$request->get('type','');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $session  = $this->get("session");
        $params=array(
            'type'=>$type,
            'mailid'=>$mailid,
            'path'=>$path,
            'mail'=>new IncomingMail(),
            'attachmentsgridStoreData'=>[],
            'fieldattachments'=>[]
            
        );
        $mailaccountid=false;
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
            $fetchedHtml='';
            if ($mail->textHtml){
                //clean html:

                $fetchedHtml=$this->cleanHtml($mail->textHtml,false,[],'On.......wrote:');
                if(strpos($fetchedHtml,$this->imageplaceholder)!==false){
                    $mail->hasexternallinks=true;
                }
            }
            //unset($mail->textHtml);
            //unset($mail->textPlain);
            $mail->textHtml=$fetchedHtml;
            $attachments=Array();
            $attachmentsgridStoreData=[];
            $fieldattachments=[];
            $fileuploads=$session->get('mailclient_fileuploads',Array());
            foreach($mail->getAttachments() as $attachment){
                $attachments[]=Array(
                    'id'=>$attachment->id,
                    'name'=>$attachment->name,
                    'filesize'=> filesize($attachment->filePath), //$attachment->fileSize,
                    'filepath'=>$this->get('kernel')->getRootDir().'/../web'.str_replace($this->attachments_dir,$this->attachmentsbase_dir,$attachment->filePath)

                );
                $newfilename=md5($user->getId().microtime().rand(0,100000));

                $fileuploads[$newfilename]=['filename'=>$attachment->name,'filepath'=>$this->get('kernel')->getRootDir().'/../web'.str_replace($this->attachments_dir,$this->attachmentsbase_dir,$attachment->filePath)];


                $attachmentsgridStoreData[]=['name'=>$attachment->name,'size'=>filesize($attachment->filePath),'hash'=>$newfilename,'status'=>100];
                $fieldattachments[]=$newfilename;
            }
            $session->set('mailclient_fileuploads',$fileuploads);
            //var_dump($fileuploads);
            $session->save();
            $mail->files=$attachments;
            $params['mail']=$mail;
            $params['attachmentsgridStoreData']=$attachmentsgridStoreData;
            $params['fieldattachments']=$fieldattachments;
        }
        $params['mail']->in_reply_to = '';
        $params['mail']->references = '';
        if ($type=='reply' || $type=='replyall'){
            $params['mail']->subject='Re: '.$params['mail']->subject;
            $from=isset($mailaccount) ? $mailaccount->getId() : null;

            $toaddr = property_exists($params['mail'], 'fromAddress') ? Array($params['mail']->fromAddress => (property_exists($params['mail'], 'fromName') ? $params['mail']->fromName : $params['mail']->fromAddress) ) : '';
            $to=$this->cleanEmailaddress($toaddr);
            if ( property_exists($params['mail'], 'replyTo') ){
                $to=$params['mail']->replyTo;
            }
            if ($type=='replyall'){
                $oldto= $params['mail']->to;

                foreach ($oldto as $key => $value){
                    if (isset($mailaccount) && $key!=$mailaccount->getAccountemail()) $to[$key] = $value;
                }

            }


            $params['mail']->from=$from;
            $params['mail']->to=$to;
            unset($params['mail']->fromName);
            unset($params['mail']->fromAddress);
            unset($params['mail']->toString);
            //unset($params['mail']->replyTo);
            $params['mail']->replyTo=[];
            if ($type=='reply'){
                $params['mail']->cc=Array();
            }
            if(isset($mail)) {
                $params['mail']->in_reply_to = $mail->headers->message_id;
                $params['mail']->references = isset($mail->headers->references) ? $mail->headers->references . ' ' . $mail->headers->message_id : $mail->headers->message_id;
            }
        }
        
        //dump($params);
        /* @var MailaccountuserRepository $repository */
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamMailclientBundle:Mailaccountuser');
        $mailaccountusers=$repository->findByUserId($user->getId());

        foreach($mailaccountusers as $mailaccountuser){
            /* @var Mailaccountuser $mailaccountuser */
            $mailaccount=$mailaccountuser->getMailaccount();
            $params['mailaccounts'][]=[
                'id'=>$mailaccount->getId(),
                'name'=>$mailaccount->getAccountname()
            ];
            if ($mailaccount->getIsdefault()){
                $params['defaultaccountid']=$mailaccount->getId();
                $params['defaultaccountname']=$mailaccount->getAccountname();
            }


        }
        return $this->render('XxamMailclientBundle:Mailclient:write.js.twig', $params);
    }
    
    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_SETTINGS')")
     *
     * @return Response
     */
    public function settingsAction() {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        /* @var MailaccountuserRepository $repository */
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamMailclientBundle:Mailaccountuser');
        $mailaccountusers=$repository->findByUserId($user->getId());
        $mas=Array();
        foreach($mailaccountusers as $mailaccountuser){
            /* @var Mailaccountuser $mailaccountuser */
            $mas[]=$mailaccountuser->getMailaccount();
        }
        return $this->render('XxamMailclientBundle:Mailclient:settings.js.twig', array('mailaccounts'=>$mas));
    }
    
}
