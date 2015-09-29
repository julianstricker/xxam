<?php

namespace Just\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Just\AdminBundle\Entity\Extjsstate;
use Just\AdminBundle\Entity\Widget;

class DefaultController extends DefaultBaseController
{
    public function indexAction()
    {
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
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
        return $this->render('JustAdminBundle:Default:index.html.twig', array('menu' => $menu));
    }
    
    public function portalAction() {
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        $widgets=$user->getWidgets();
        return $this->render('JustAdminBundle:Default:portal.js.twig', array('widgets'=>$widgets));
    }
    
    
    public function uploadfileprogressAction(Request $request) {
        $progressid=$request->get('progressid','');
        //echo 'upload_progress_'.$progressid;
        dump(session_name());
        dump($_SESSION);
        $response = new Response(json_encode(Array('status'=>'OK','uploadstatus'=>$this->get('session')->get("upload_progress_".$progressid))));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response; 
    }
    
    public function addwidgetAction() {
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        return $this->render('JustAdminBundle:Default:addwidget.js.twig', array());
    }
    
    public function doaddwidgetAction(Request $request) {
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
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
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        $widget=$this->getDoctrine()->getManager()->getRepository('JustAdminBundle:Widget')->findOneBy(Array('id'=>$id,'user_id'=>$user->getId()));
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
        $widget=$this->getDoctrine()->getManager()->getRepository('JustAdminBundle:Widget')->find($id);
        //$this->forward($widget->getService().':indexAction', array('params' => $widget->getParams()));
        $template=$this->get($widget->getService())->getWidgetTemplate();
        return $this->render($template, array('params' => json_decode($widget->getParams()),'id'=>$widget->getId()));
    }
    
    /*
     * Get list of all available Widgets:
     */
    public function getwidgetsAction(){
        $returnvalue=Array();
        $registeredwidgets=$this->getRegisteredWidgets();
        foreach($registeredwidgets as $registeredwidget){
            $definition=$this->get($registeredwidget)->getDefinition();
            $definition['service']=$registeredwidget;
            $returnvalue[]=$definition;
        }
        $response = new Response(json_encode(Array('widgets'=>$returnvalue)));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response; 
    }
    
    
    
    /*
     * save/load/delete Ext-Js Stateful-Settings 
     */
    public function statefulserviceAction(Request $request){
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        if($request->getMethod()=='GET'){
            $states=$user->getExtjsstates();
            $resp=Array();
            foreach($states as $state){
              $resp[$state->getStatekey()]=$state->getStatevalue();
            }
            $response = new Response(json_encode($resp));
            $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
            return $response; 
        }else if($request->getMethod()=='POST'){
            $state=$widget=$this->getDoctrine()->getManager()->getRepository('JustAdminBundle:Extjsstate')->findOneBy(Array('user_id'=>$user->getId(),'statekey'=>$request->get('key')));
            if(!$state){
                $state=new Extjsstate();
                $state->setUser($user);
                $state->setStatekey($request->get('key'));
            }
            $state->setStatevalue($request->get('value'));
            $em->persist($state);
            $em->flush();
            $resp=Array('success'=>'true');
            
            $response = new Response(json_encode($resp));
            $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
            return $response; 
        }else if($request->getMethod()=='DELETE'){
            $filter=Array('user_id'=>$user->getId());
            if ($request->get('key',false)!=false) $filter['statekey']=$request->get('key');
            $states=$widget=$this->getDoctrine()->getManager()->getRepository('JustAdminBundle:Extjsstate')->findBy($filter);
            if($states){
                foreach($states as $state){
                    $em->remove($state);
                }
            }
            $em->flush();
            $resp=Array('success'=>'true');
            
            $response = new Response(json_encode($resp));
            $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
            return $response; 
        }
        
    }
}
