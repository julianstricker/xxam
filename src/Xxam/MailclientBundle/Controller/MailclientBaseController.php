<?php

namespace Xxam\MailclientBundle\Controller;

use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Xxam\MailclientBundle\Entity\Mailaccountuser;
use Xxam\MailclientBundle\Entity\MailaccountuserRepository;
use Xxam\MailclientBundle\Helper\Imap\ImapMailbox;
use Xxam\MailclientBundle\Entity\Mailaccount;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class MailclientBaseController extends Controller {
    
    protected $attachments_dir;
    protected $attachmentsbase_dir;
    protected $imageplaceholder='/bundles/xxammailclient/images/blocked.gif';
    
    protected function getUserMailaccountForId($mailaccountid){
        $user = $this->get('security.token_storage')->getToken()->getUser();
        /* @var MailaccountuserRepository $repository */
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamMailclientBundle:Mailaccountuser');
        $mailaccountusers=$repository->findByUserId($user->getId());
        $mailaccount=null;
        foreach ($mailaccountusers as $mau){
            /* @var Mailaccountuser $mau */
            $ma=$mau->getMailaccount();
            if($ma->getId()==$mailaccountid){
                $mailaccount=$ma;
                break;
            }
        }
        return $mailaccount;
    }

    protected function getMailaccountidFromPath( $path){
        if ($path){
            $pathexpl=explode('.',$path);
            return $pathexpl[0];
        }
        return false;
    }

    protected function removeMailaccountidFromPath( $path){
        $pathexpl=explode('.',$path);
        unset($pathexpl[0]);
        $path=count($pathexpl)>0 ? '.'.implode('.',$pathexpl) : '';
        return $path;
    }

    protected function getImapMailboxForPath( $path){
        $mailaccountid=$this->getMailaccountidFromPath($path);
        $from=$this->removeMailaccountidFromPath($path);
        $mailaccount=$this->getUserMailaccountForId($mailaccountid);
        if (!$mailaccount){
            return false; //$this->throwJsonError('Mailaccount not found');
        }
        return $this->getImapMailbox($mailaccount,$from);
    }

    protected function getJsonResponse( $data)
    {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

    protected function removeChildrenkeys($children){
        if (isset($children['children'])){
            $children['children']=array_values($children['children']);
            foreach($children['children'] as $key => $child){
                $children['children'][$key] = $this->removeChildrenkeys($child);
            }
        }
        return $children;
    }
    
    
    protected function cleanEmailaddress(Array $emails){
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
     * @param Mailaccount $mailaccount
     * @param string $path
     *
     * @return ImapMailbox
     *
     */
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
        $connectionstring="{".$mailaccount->getImapserver().":".($mailaccount->getImapport() ? $mailaccount->getImapport() : 143).$securitystring."}".$mailaccount->getImappathprefix().$path;
        $this->attachments_dir = realpath($this->get('kernel')->getRootDir() . '/../web/uploads/attachments/'.$mailaccount->getId());
        $this->attachmentsbase_dir = '/uploads/attachments/'.$mailaccount->getId();
        if (!is_dir($this->attachments_dir)){
            mkdir($this->attachments_dir);
        }
        return new ImapMailbox($connectionstring, $mailaccount->getImapusername(), $mailaccount->getImappassword(), $this->attachments_dir, 'UTF-8');
    }
    
    protected function removeHtmltag( \DOMDocument &$doc, $tagname){
        $tags=$doc->getElementsByTagName($tagname);

        // for each tag, remove it from the DOM


        if ($tags->length>0){
            for ($i = $tags->length; --$i >= 0; ) {
                $tag= $tags->item($i);
                  $tag->parentNode->removeChild($tag);
            }
        }
        //return $doc;
    }


    /**
     * Get array of internal HTML links placeholders
     * @param string $textHtml
     * @return array attachmentId => link placeholder
     */
    public function getInternalLinksPlaceholders($textHtml) {
        return preg_match_all('/=["\'](ci?d:([\w\.%*@-]+))["\']/i', $textHtml, $matches) ? array_combine($matches[2], $matches[1]) : array();

    }

    /**
     * @param string $textHtml
     * @param string $baseUri
     * @param array $attachments
     * @return mixed
     */
    public function replaceInternalLinks($textHtml, $baseUri, $attachments) {
        $baseUri = rtrim($baseUri, '\\/') . '/';
        $fetchedHtml = $textHtml;
        foreach($this->getInternalLinksPlaceholders($textHtml) as $attachmentId => $placeholder) {
            if(isset($attachments[$attachmentId])) {
                $fetchedHtml = str_replace($placeholder, $baseUri . basename($attachments[$attachmentId]->filePath), $fetchedHtml);
            }
        }
        return $fetchedHtml;
    }

    /**
     * @param string $textHtml
     * @param bool $externalsources
     * @param array $attachments
     * @param string $quotetext
     * @return mixed|string
     */
    protected function cleanHtml($textHtml, $externalsources, $attachments, $quotetext=''){
        
        $fetchedHtml=$this->replaceInternalLinks($textHtml,$this->attachmentsbase_dir,$attachments);
        
        if (!$externalsources){
            $baseUriesc=str_replace('/','\/', $this->attachmentsbase_dir);
            $fetchedHtml=preg_replace('/(src[\s]*=[\s]*["|\'])((?!'.$baseUriesc.')[^"|\']+)("|\')/i', "$1".$this->imageplaceholder. "$3", $fetchedHtml);
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
        $encoding=$doc->encoding;
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
            $style->setAttribute('href',"/bundles/xxammailclient/css/mailbody.css");
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
    
    protected function throwJsonError($errormessage) {
        $response = new Response(json_encode(Array('error' => $errormessage)));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        $response->setStatusCode(500);
        return $response;
    }

    /*
     * Create Swiftmailer-Class for Mailaccount
     */
    protected function getMailerForMailaccount(Mailaccount $mailaccount){
        // switch to new settings
        $transport = \Swift_SmtpTransport::newInstance($mailaccount->getSmtpserver(), $mailaccount->getSmtpport() != '' ? $mailaccount->getSmtpport() : 25)->setUsername($mailaccount->getSmtpusername())->setPassword($mailaccount->getSmtppassword());
        if ($mailaccount->getSmtpsecurity()!=0) $transport->setEncryption($mailaccount->getSmtpsecurity()==1 || $mailaccount->getSmtpsecurity()==2 ? 'ssl' : 'tls');

        $mailer = \Swift_Mailer::newInstance($transport);
        $logger = new \Swift_Plugins_Loggers_ArrayLogger();
        $mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($logger));
        return $mailer;
    }

    protected function addInlineImagesToMessage(\Swift_Message &$message, $fieldhtml){
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

        $message->setContentType("text/html")->setBody($fieldhtml, 'text/html');
        return true;
    }

    protected function addAttachmentsToMessage(\Swift_Message &$message, $fieldattachments){
        if ($fieldattachments != ''){
            $attachments=json_decode($fieldattachments);
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $session  = $this->get("session");
            $fileuploads=$session->get('mailclient_fileuploads',Array());

            if (count($attachments)>0){
                foreach($attachments as $attachment){
                    if (isset($fileuploads[$attachment])){
                        $filename=$fileuploads[$attachment]['filename'];
                        $file=$fileuploads[$attachment]['filepath'];
                    }else{
                        $file=$this->get('kernel')->getCacheDir() . '/mailclient_fileuploads/'.$user->getId().'/'.$attachment;
                        $filename=$attachment;
                    }
                    $message->attach(\Swift_Attachment::fromPath($file)->setFilename($filename));
                }
            }
        }
        return true;
    }

    /**
     * @param Request $request
     * @return \Swift_Message|Response
     */
    protected function generateMailForRequest(Request $request){
        $fieldfrom=$request->get('fieldfrom',0);
        $mailaccount=$this->getUserMailaccountForId($fieldfrom);
        if (!$mailaccount){
            return $this->throwJsonError('Mailaccount not found');
        }

        /* @var \Swift_Message $message */
        $message = \Swift_Message::newInstance()
            ->setSubject($request->get('fieldsubject',''))
            ->setFrom(array($mailaccount->getAccountemail()=>$mailaccount->getName()));
        try {
            if ($request->get('fieldreplyto',false)) $message->setReplyTo($this->cleanEmailaddress($request->get('fieldreplyto',false)));
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
            $this->addInlineImagesToMessage($message,$fieldhtml);
            if ($request->get('fieldtext','') != '') $message->addPart($request->get('fieldtext',''), 'text/plain');
        }else{
            if ($request->get('fieldtext','') != '') $message->setContentType("text/plain")->setBody($request->get('fieldtext',''), 'text/plain');
        }
        $headers = $message->getHeaders();
        if ($request->get('fieldinreplyto','')!='') {

            $inreplyto=$headers->get('in_reply_to');
            if ($inreplyto){
                $inreplyto->setValue($request->get('fieldinreplyto',''));
            }else{
                $headers->addTextHeader('in_reply_to', $request->get('fieldinreplyto',''));
            }
        }
        if ($request->get('fieldreferences','')!='') {
            $references=$headers->get('references');
            if ($references){
                $references->setValue($request->get('fieldreferences',''));
            }else{
                $headers->addTextHeader('references', $request->get('fieldreferences',''));
            }
        }

        //attachments:
        $this->addAttachmentsToMessage($message,$request->get('fieldattachments',''));

        return $message;

    }

    protected function sendmail(Swift_Mailer $mailer, \Swift_Message $message){
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
        return true;
    }
}
