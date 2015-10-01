<?php

namespace Just\MailclientBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class NewmailsWidgetController extends Controller
{
    private $templating;
    private $securityContext;
    public function __construct($templating,$securityContext)
    {
        $this->templating = $templating;
        $this->securityContext = $securityContext;
    }

    
    /*public function indexAction($params)
    {
        return $this->templating->renderResponse(
                'JustMailclientBundle:FeedWidget:index.js.twig', 
                array('params' => $params)
        );
        
    }*/

    //this function is required for every portalwidget:
    public function getWidgetTemplateAction(){
        return 'JustMailclientBundle:NewmailsWidget:index.js.twig';
    }


    //this function is required for every portalwidget:
    public function getDefinitionAction()
    {
        
        $user = $this->securityContext->getToken()->getUser();
        $mailaccounts=Array();
        foreach($user->getMailaccounts() as $ma){
            $mailaccounts[]=Array($ma->getId(),$ma->getAccountname());
        }
        return Array(
            'title'=>'Emails',
            'description'=>'Displays Emails',
            'icon'=> '/bundles/justmailclient/icons/32x32/email.png',
            //'code' => 'feed',
            
            'settings'=>Array(
                Array(
                  'fieldLabel'=> 'Account',
                  'name'=> 'account',
                  'xtype' =>  'combo',
                  'store' => $mailaccounts,
                  'allowBlank' => false
                )
            )
            
        );
    }
    
    public function loadfeedAction($url){
        $response = new Response(str_replace(array("dc:creator","content:encoded"), array('author','content'), file_get_contents(urldecode($url))));
        $response->headers->set('Content-Type', 'text/xml; charset=UTF-8');
        return $response;
    }
}
