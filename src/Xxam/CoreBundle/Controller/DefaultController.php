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

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Xxam\CoreBundle\Entity\LogEntryRepository;
use Xxam\CoreBundle\Entity\Widget;
use Xxam\UserBundle\Entity\User;

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
        $memcached = $this->get('memcached');
        $memcached->add('chatid_'.$token,$userdata);
        $environment= $this->container->get( 'kernel' )->getEnvironment();
        $exttheme=$this->getParameter('exttheme');
        /*$extthemecss='/assets/vendor/extjs/build/packages/ext-theme-'.$exttheme.'/build/resources/ext-theme-'.$exttheme.'-all.css';
        $extthemejs='/assets/vendor/extjs/build/packages/ext-theme-'.$exttheme.'/build/ext-theme-'.$exttheme.'.js';
        if ($exttheme=='carbon'){
            $extthemecss='/ext-theme-carbon/resources/codaxy-theme-carbon-all.css';
            $extthemejs='/ext-theme-carbon/codaxy-theme-carbon.js';
        }*/
        return $this->render('XxamCoreBundle:Default:index.html.twig', array(
            'menu' => $menu,
            'token'=>$token,
            'tenant_id'=>$request->getSession()->get('tenant_id'),
            'exttheme'=> $exttheme,

        ));
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
     * Removes a Widget Component.
     *
     * @param int $id
     * @return Response
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
     * gets the Widget Component.
     *
     * @param int $id
     * @return Response
     */
    public function getwidgetAction($id) {
        $widget=$this->getDoctrine()->getManager()->getRepository('XxamCoreBundle:Widget')->find($id);
        //$this->forward($widget->getService().':indexAction', array('params' => $widget->getParams()));
        $template=$this->get($widget->getService())->getWidgetTemplateAction();
        return $this->render($template, array('params' => json_decode($widget->getParams()),'id'=>$widget->getId()));
    }

    /**
     * Get list of all available Widgets:
     *
     * @return Response
     */
    public function getwidgetsAction(Request $request){
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
        return true;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function getlogentriesAction(Request $request) {

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $entityname=$request->get('entityname','');
        $id=$request->get('id',null);
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var LogEntryRepository $logrepo */
        $logrepo=$em->getRepository('XxamCoreBundle:LogEntry');
        $entity= $em->find($entityname,$id);
        $logs = $logrepo->getLogEntries($entity);
        $returnvalues=[];
        foreach($logs as $log){
            $returnvalues[]=[
                'logged_at'=>$log->getLoggedAt()->format('Y-m-d H:i:s'),
                'action'=>$log->getAction(),
                'object_id'=>$log->getObjectId(),
                'username'=>$log->getUsername(),
                'version'=>$log->getVersion()
            ];
        }

        $response = new Response(json_encode(Array('logentries'=>$returnvalues)));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }
}
