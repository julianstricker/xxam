<?php

namespace Just\MailclientBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Just\MailclientBundle\Helper\Imap\ImapMailbox;
use Just\MailclientBundle\Entity\Mailaccount;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpFoundation\Session;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class MailclientController extends Controller {
    
    
    private function getUserMailaccountForId($mailaccountid){
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        $mailaccount=false;
        foreach($user->getMailaccounts() as $ma){
            if($ma->getId()==$mailaccountid){
                $mailaccount=$ma;
                break;
            }
        }
        return $mailaccount;
    }

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
    
    private function removeChildrenkeys($children){
        if (isset($children['children'])){
            $children['children']=array_values($children['children']);
            foreach($children['children'] as $key => $child){
                $children['children'][$key] = $this->removeChildrenkeys($child);
            }
        }
        return $children;
    }
    private $attachments_dir;
    private $attachmentsbase_dir;
    
    private function cleanEmailaddress($emails){
        $return=Array();
        if (!is_array($emails)){
            $emails=Array($emails);
        }
        foreach($emails as $email){
            if (strpos($email, '<')!==false){
                $emailexpl=explode('<',$email);
                $return[str_replace('>','',$emailexpl[1])]=trim($emailexpl[0]);
            }else{
                $return[$email]=$email;
            }
        }
        return $return;
    }
    
    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_LIST')")
     *
     */
    private function getImapMailbox(Mailaccount $mailaccount,$path='')
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
        $connectionstring="{".$mailaccount->getImapserver().":".($mailaccount->getImapport() ? $mailaccount->getImapport() : 143).$securitystring."}".$mailaccount->getImappathprefix().$path;
        $this->attachments_dir = realpath($this->get('kernel')->getRootDir() . '/../web/uploads/attachments/'.$mailaccount->getId());
        $this->attachmentsbase_dir = '/uploads/attachments/'.$mailaccount->getId();
        if (!is_dir($this->attachments_dir)){
            mkdir($this->attachments_dir);
        }
        return new ImapMailbox($connectionstring, $mailaccount->getImapusername(), $mailaccount->getImappassword(), $this->attachments_dir, 'UTF-8');
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
    
    private $imageplaceholder='/bundles/justmailclient/images/blocked.gif';
    
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
    
    private function removeHtmltag(&$doc,$tagname){
        $tags=$doc->getElementsByTagName($tagname);
        $length = $tags->length;
        // for each tag, remove it from the DOM
        if ($length>0){
            for ($i = 0; $i < $length; $i++) {
              if($tags->item($i)->parentNode){ 
                  $tags->item($i)->parentNode->removeChild($tags->item($i));
              }
            }
        }
        //return $doc;
    }
    
    private function cleanHtml($mail,$externalsources,$quotetext=''){
        
        $fetchedHtml=$mail->replaceInternalLinks($this->attachmentsbase_dir);
        
        if (!$externalsources){
            $baseUriesc=str_replace('/','\/', $this->attachmentsbase_dir);
            $fetchedHtml=preg_replace('/(src[\s]*=[\s]*\")((?!'.$baseUriesc.')[^"]+)(\")/i', "$1".$this->imageplaceholder."\"", $fetchedHtml);
            $fetchedHtml=preg_replace('/(url[\s]*\()((?!'.$baseUriesc.')[^\)]+)(\))/i', "$1'".$this->imageplaceholder."'$3", $fetchedHtml);
        }
        
        //metas:
        //$fetchedHtml=preg_replace('/<meta[^>]*>/i', "", $fetchedHtml);
        //$fetchedHtml=preg_replace('/<body[^>]*>/is', "", $fetchedHtml);
        
//        $fetchedHtml=preg_replace('/<\/body[^>]*>/i', "", $fetchedHtml);
//        $fetchedHtml=preg_replace('/<html[^>]*>/i', "", $fetchedHtml);
//        $fetchedHtml=preg_replace('/<\/html[^>]*>/i', "", $fetchedHtml);
//        echo $fetchedHtml;
//        die();
        //echo $mail->textHtml;
//            echo $fetchedHtml;
//            die();
//            //clean html:
            
        $oldhtml='';
        $newhtml=$fetchedHtml;
        //return $newhtml;
        //nur zum testen:
        $doc = new \DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $doc->loadHTML($newhtml);
        libxml_clear_errors();
        $encoding=$doc->actualEncoding;
        if ($encoding==NULL) {
            $encoding='UTF-8';
            $convhtml=iconv('UTF-8',$encoding,$newhtml);
            //return $convhtml;
            $doc = new \DOMDocument('1.0');
            $doc->encoding = 'utf-8';
        
            $doc->formatOutput=false;
            $doc->resolveExternals = true;
            $doc->substituteEntities=false;
            libxml_use_internal_errors(true);
            $doc->loadHTML(utf8_decode($convhtml));
            libxml_clear_errors();
            //$this->removeHtmltag($doc,'meta');
            $newhtml=$doc->saveHTML();
        }else{
            $convhtml=iconv('UTF-8',$encoding,$newhtml);
            //return $convhtml;
            $doc = new \DOMDocument('1.0', $encoding);
            $doc->formatOutput=false;
            $doc->resolveExternals = true;
            $doc->substituteEntities=false;
            libxml_use_internal_errors(true);
            $doc->loadHTML(!$convhtml ? $newhtml : $convhtml);
            libxml_clear_errors();
            //$this->removeHtmltag($doc,'meta');
            $newhtml=$doc->saveHTML();
        }
        //}
        //return $newhtml;
        //$newhtml=iconv($encoding,'UTF-8',$newhtml);
        
//        $doc = new \DOMDocument('1.0', $encoding);
//        libxml_use_internal_errors(true);
//
//        $doc->loadHTML($newhtml);
//        libxml_clear_errors();
        //
        
        //return $doc->actualEncoding.' '.$doc->saveHTML();
        //ende testen;
        
        
        
        while($oldhtml!=$newhtml){
            $oldhtml=$newhtml;
//            echo $newhtml;
//            echo "\n".'XXXXXXXXXXXXXXXXXXXXXXXXXXX'."\n";
            $doc = new \DOMDocument('1.0', 'UTF-8');
            libxml_use_internal_errors(true);
            
            $doc->loadHTML($newhtml);
            libxml_clear_errors();
//            $body=$doc->getElementsByTagName('body');
//            if ($body->length>0) $doc=$body->item(0);
            
            $this->removeHtmltag($doc,'script');
            $this->removeHtmltag($doc,'head');
            $this->removeHtmltag($doc,'meta');
            $newhtml = $doc->saveHTML();
        }
            
            
        
            $body=$doc->getElementsByTagName('body')->item(0);
            $head = $doc->createElement('head'); 
            $base=$doc->createElement('base');
            $base->setAttribute('target',"_blank");
            $head->appendChild($base);
            $style=$doc->createElement('link');
            $style->setAttribute('rel',"stylesheet");
            $style->setAttribute('type',"text/css");
            $style->setAttribute('href',"/bundles/justmailclient/css/mailbody.css");
            $style->setAttribute('media',"screen");
            $head->appendChild($style);
            $html= $doc->getElementsByTagName('html')->item(0);
            if ($quotetext!=''){
                $bqnode = $doc->createElement("blockquote");
                //if ($body->hasChildNodes()){
                    while($body->childNodes->length){
                        //echo 'YY'.$body->textContent;
                        $child=$body->removeChild($body->firstChild);
                        $bqnode->appendChild($child);
                    }
                //}
                $qtext=$doc->createElement('p');
                $qtext->appendChild($doc->createTextNode($quotetext));
                $body->appendChild($qtext);
                $body->appendChild($bqnode);
            }
            $html->insertBefore($head,$body);
            $newhtml = $doc->saveHTML();
        
        return $newhtml;
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
    
    private function throwJsonError($errormessage) {
        $response = new Response(json_encode(Array('error' => $errormessage)));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        $response->setStatusCode(500);
        return $response;
    }
}
