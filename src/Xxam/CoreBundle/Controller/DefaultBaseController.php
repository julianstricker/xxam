<?php

namespace Xxam\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Xxam\CoreBundle\Entity\Extjsstate;

class DefaultBaseController extends Controller
{
    
    
    protected function getRegisteredWidgets(){
        $widgets=Array();
        foreach($this->container->getServiceIds() as $serviceid){
            if (strpos($serviceid,'xxamportalwidget.')===0){
                $widgets[]=$serviceid;
            }
        }
        return $widgets;
    }
    
    protected function getMenu($config)
    {
        $menu=Array();
	foreach($config as $value){
            $menuitem=Array();
            foreach($value as $key=>$val){
                if ($key=='role'){
                    if (!$this->get('security.authorization_checker')->isGranted($val)) continue 2;
                }elseif($key=='menu') {
                    $menuitem[$key]=$this->getMenu($val);
                }else{
                    $menuitem[$key]=$val;
                }
            }
	    
	    $menu[]=$menuitem;
        }
	return $menu;
    }

    protected function statefulGetResponse($user){
        $states=$user->getExtjsstates();
        $resp=Array();
        foreach($states as $state){
            $resp[$state->getStatekey()]=$state->getStatevalue();
        }
        $response = new Response(json_encode($resp));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

    protected function statefulPostResponse($user,$key,$value){
        $em = $this->getDoctrine()->getManager();
        $state=$widget=$this->getDoctrine()->getManager()->getRepository('XxamCoreBundle:Extjsstate')->findOneBy(Array('user_id'=>$user->getId(),'statekey'=>$key));
        if(!$state){
            $state=new Extjsstate();
            $state->setUser($user);
            $state->setStatekey($key);
        }
        $state->setStatevalue($value);
        $em->persist($state);
        $em->flush();
        $resp=Array('success'=>'true');

        $response = new Response(json_encode($resp));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

    protected function statefulDeleteResponse($user,$key){
        $em = $this->getDoctrine()->getManager();
        $filter=Array('user_id'=>$user->getId());
        if ($key!=false) $filter['statekey']=$key;
        $states=$widget=$this->getDoctrine()->getManager()->getRepository('XxamCoreBundle:Extjsstate')->findBy($filter);
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
