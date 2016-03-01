<?php

/*
 * This file is part of the Xxam package.
 *
 * (c) Julian Stricker <julian@julianstricker.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xxam\MailclientBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Xxam\MailclientBundle\Entity\Mailaccountuser;
use Xxam\MailclientBundle\Entity\MailaccountuserRepository;


class NewmailsWidgetController extends Controller
{
    private $securityTokenStorage;
    private $entityManager;

    /**
     * NewmailsWidgetController constructor.
     * @param TokenStorage $securityTokenStorage
     * @param EntityManager $entityManager
     */
    public function __construct($securityTokenStorage,$entityManager)
    {
        $this->securityTokenStorage = $securityTokenStorage;
        $this->entityManager = $entityManager;
    }


    /**
     * this function is required for every portalwidget:
     *
     * @return string
     */
    public function getWidgetTemplateAction(){
        return 'XxamMailclientBundle:NewmailsWidget:index.js.twig';
    }


    //this function is required for every portalwidget:
    public function getDefinitionAction()
    {

        $user = $this->securityTokenStorage->getToken()->getUser();

        $repository = $this->entityManager->getRepository('XxamMailclientBundle:Mailaccountuser');
        /* @var MailaccountuserRepository $repository */
        $mailaccountusers=$repository->findByUserId($user->getId());
        $mailaccounts=Array();
        foreach($mailaccountusers as $mau){
            /* @var Mailaccountuser $mau */
            $ma=$mau->getMailaccount();
            $mailaccounts[]=Array($ma->getId(),$ma->getAccountname());
        }
        return Array(
            'title'=>'Emails',
            'description'=>'Displays Emails',
            'icon'=> '/bundles/xxammailclient/icons/32x32/email.png',
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
