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
use Symfony\Component\HttpFoundation\Request;
use Xxam\CoreBundle\Entity\Extjsstate;
use Xxam\CoreBundle\Entity\Widget;

/*
 * Class DefaultController
 * This is the Default Controller for Xxam
 *
 * @package Xxam\CoreBundle\Controller
 * @author Julian Stricker <julian@julianstricker.com>
 */

class DefaultController extends DefaultBaseController
{
    public function indexAction(Request $request)
    {

        $user = $this->get('security.token_storage')->getToken()->getUser();
        //generate menu:
        $menuitems=Array();
        foreach ($this->container->getParameter('kernel.bundles') as $name ) {
            if(method_exists($name,'getMenu')){
                $menuitems=array_replace_recursive($menuitems,call_user_func($name.'::getMenu'));
            }
        }
        //dump($menuitems);
        $menu=$this->getMenu($menuitems);
        $menu[]=Array('xtype'=>'tbfill','stateId'=>'xxam_menu_tbfill');
        $menu[]=Array(
            'text'=>$this->get('translator')->trans('Logged in as %username%',Array('%username%'=>$user->getUsername())),
            'stateId'=>'xxam_menu_user',
            'menu'=>Array(
                Array(
                    'text'=>       'Logout',
                    'iconCls'=>    'defaultmenuicon',
                    'href'=>       $this->generateUrl('fos_user_security_logout')
                )
            )
        );

        //generate user token for chat:
        $key =$this->container->getParameter('secret');
        $userdata = array(
            "tenant_id" => $user->getTenantId(),
            "user_id" => $user->getId(),
            "username" => $user->getUsername(),
            //"authid" => $user->getTenantId().'|'.$user->getId().'|'.$user->getUsername()
        );
        $token=hash('sha256',rand(0,99999999999).$key);
        $memcached=new \Memcached();
        $memcached->addServer('localhost', 11211);
        $memcached->add('chatid_'.$token,$userdata);

        return $this->render('XxamCoreBundle:Default:index.html.twig', array('menu' => $menu,'token'=>$token,'tenant_id'=>$request->getSession()->get('tenant_id') ));
    }

    public function portalAction() {

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $widgets=$user->getWidgets();
        return $this->render('XxamCoreBundle:Default:portal.js.twig', array('widgets'=>$widgets));
    }


    public function uploadfileprogressAction(Request $request) {
        $progressid=$request->get('progressid','');
        $response = new Response(json_encode(Array('status'=>'OK','uploadstatus'=>$this->get('session')->get("upload_progress_".$progressid))));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

    public function addwidgetAction() {

        $user = $this->get('security.token_storage')->getToken()->getUser();
        return $this->render('XxamCoreBundle:Default:addwidget.js.twig', array());
    }

    public function doaddwidgetAction(Request $request) {

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $widget=new Widget();
        $widget->setUser($user);
        $widget->setService($request->get('service'));
        $widget->setTitle($request->get('title'));
        $widget->setSortfield(1);
        $definition=$this->get($request->get('service'))->getDefinition();
        $params=Array();
        foreach($definition['settings'] as $setting){
            $params[$setting['name']]=$request->get($setting['name']);
        }
        $widget->setParams(json_encode($params));
        $em = $this->getDoctrine()->getManager();
        $em->persist($widget);
        $em->flush();
        $widgetdata=Array('id'=>$widget->getId(), 'title'=>$widget->getTitle(), 'col'=>$widget->getCol(), 'sortfield'=>$widget->getSortfield(), 'params'=>$widget->getParams());

        $response = new Response(json_encode(Array('success'=>true,'widget'=>$widgetdata)));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

    /**
     * Loads the Widget Component.
     */
    public function removewidgetAction($id) {

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $widget=$this->getDoctrine()->getManager()->getRepository('XxamCoreBundle:Widget')->findOneBy(Array('id'=>$id,'user_id'=>$user->getId()));
        $this->getDoctrine()->getManager()->remove($widget);
        $this->getDoctrine()->getManager()->flush();
        $response = new Response(json_encode(Array('success'=>true)));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

    /**
     * Loads the Widget Component.
     */
    public function getwidgetAction($id) {
        $widget=$this->getDoctrine()->getManager()->getRepository('XxamCoreBundle:Widget')->find($id);
        //$this->forward($widget->getService().':indexAction', array('params' => $widget->getParams()));
        $template=$this->get($widget->getService())->getWidgetTemplateAction();
        return $this->render($template, array('params' => json_decode($widget->getParams()),'id'=>$widget->getId()));
    }

    /*
     * Get list of all available Widgets:
     */
    public function getwidgetsAction(){
        $returnvalue=Array();
        $registeredwidgets=$this->getRegisteredWidgets();
        foreach($registeredwidgets as $registeredwidget){
            $definition=$this->get($registeredwidget)->getDefinitionAction();
            $definition['service']=$registeredwidget;
            $returnvalue[]=$definition;
        }
        $response = new Response(json_encode(Array('widgets'=>$returnvalue)));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }




    /**
     * save/load/delete Ext-Js Stateful-Settings
     *
     * @param Request $request
     * @return Response
     */
    public function statefulserviceAction(Request $request){

        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if($request->getMethod()=='GET'){
            return $this->statefulGetResponse($user);
        }else if($request->getMethod()=='POST'){
            return $this->statefulPostResponse($user,$request->get('key',false),$request->get('value'));
        }else if($request->getMethod()=='DELETE'){
            return $this->statefulDeleteResponse($user,$request->get('key',false));
        }

    }
}
