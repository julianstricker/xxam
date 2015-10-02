<?php

namespace Just\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Just\AdminBundle\Helper\Imap\ImapMailbox;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class MailclientController extends Controller {

    public function indexAction() {
        return $this->render('JustAdminBundle:Mailclient:index.js.twig', array());
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
    
    public function listfoldersAction() {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $mailaccounts=$user->getMailaccounts();
        $returndata=Array();
        foreach($mailaccounts as $mailaccount){
            
            $attachments_dir = $this->get('kernel')->getRootDir() . '/../web/uploads' . '/attachments';
            $mailbox = new ImapMailbox($mailaccount->getConnectionstring(), $mailaccount->getUsername(), $mailaccount->getPassword(), $attachments_dir, 'utf-8');
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
                            'leaf' => true
                        );
                        unset($subfolder['leaf']);
                    }
                    $subfolder = &$subfolder['children'][$fexp];
                }
            }
            $children['expanded']=true;
            $children['text']= $mailaccount->getUsername();
            $children['path']=$mailaccountid;  //<-account_id


            $returndata[] =  $this->removeChildrenkeys($children);
        }
        //dump($returndata);
        //setLocale(LC_ALL,'de_DE.UTF8');
        $response = new Response(json_encode($returndata));
        $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
        return $response;
    }
    public function listmailsAction(Request $request){
        $attachments_dir = $this->get('kernel')->getRootDir() . '/../web/uploads' . '/attachments';
        $page=$request->get('page',1);
        $start=$request->get('start',0);
        $limit=$request->get('limit',100);
        $path=$request->get('path','');
        
        if ($path){
            $pathexpl=explode('.',$path);
            $mailaccountid=$pathexpl[0];
            unset($pathexpl[0]);
            $path=count($pathexpl)>0 ? '.'.implode('.',$pathexpl) : '';
        }
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $mailaccount=null;
        foreach($user->getMailaccounts() as $ma){
            if($ma->getId()==$mailaccountid){
                $mailaccount=$ma;
                break;
            }
        }
        $returndata=Array();
        $totalcount=0;
        if($mailaccount){
            $mailbox = new ImapMailbox($mailaccount->getConnectionstring().($path!='' ? $path : ''), $mailaccount->getUsername(), $mailaccount->getPassword(), $attachments_dir, 'utf-8');
            $searchcriteria='ALL'; //'ALL';
            $mails_ids = $mailbox->sortMails(SORTARRIVAL, false, $searchcriteria); 
            $totalcount=count($mails_ids);
            $returndata=$mailbox->getMailsInfo(array_slice($mails_ids,$start,$limit));
        }
        
        $response = new Response(json_encode(Array('totalCount'=>$totalcount,'mails'=>$returndata)));
        $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
        return $response; 
    }

}
