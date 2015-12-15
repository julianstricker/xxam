<?php

/*
 * This file is part of the Xxam package.
 *
 * (c) Julian Stricker <julian@julianstricker.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xxam\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class FeedWidgetController
 * @package Xxam\CoreBundle\Controller
 */
class FeedWidgetController extends Controller
{


    /**
     * Returns the Portal Widget Template name
     * this function is required for every portalwidget
     *
     * @return string
     */
    public function getWidgetTemplateAction(){
        return 'XxamCoreBundle:FeedWidget:index.js.twig';
    }

    /**
     * Returns the Portal Widget Definitions
     * this function is required for every portalwidget
     *
     * @return array
     */
    public function getDefinitionAction()
    {
        return Array(
            'title'=>'Feed',
            'description'=>'Displays the content of a RSS-Feed',
            'icon'=> '/bundles/xxamcore/icons/32x32/rss.png',
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

    /**
     * Returns the data of a RSS-Feed
     *
     * @param string $url
     * @return Response
     */
    public function loadfeedAction($url){
        $response = new Response(str_replace(array("dc:creator","content:encoded"), array('author','content'), file_get_contents(urldecode($url))));
        $response->headers->set('Content-Type', 'text/xml; charset=UTF-8');
        return $response;
    }
}
