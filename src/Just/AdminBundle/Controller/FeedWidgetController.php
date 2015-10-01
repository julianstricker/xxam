<?php

namespace Just\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class FeedWidgetController extends Controller
{
    /*private $templating;

    public function __construct($templating)
    {
        $this->templating = $templating;
    }*/


    /*public function indexAction($params)
    {
        return $this->templating->renderResponse(
                'JustAdminBundle:FeedWidget:index.js.twig',
                array('params' => $params)
        );

    }*/

    //this function is required for every portalwidget:
    public function getWidgetTemplateAction(){
        return 'JustAdminBundle:FeedWidget:index.js.twig';
    }


    //this function is required for every portalwidget:
    public function getDefinitionAction()
    {
        return Array(
            'title'=>'Feed',
            'description'=>'Displays the content of a RSS-Feed',
            'icon'=> '/bundles/justadmin/icons/32x32/rss.png',
            //'code' => 'feed',

            'settings'=>Array(
                Array(
                  'fieldLabel'=> 'URL',
                  'name'=> 'url',
                  'xtype' =>  'textfield',
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
