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
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Response;
use Xxam\CoreBundle\Entity\Extjsstate;
use Xxam\UserBundle\Entity\User;

/*
 * Class DefaultBaseController
 * This is the Default Base Controller for Xxam
 *
 * @package Xxam\CoreBundle\Controller
 * @author Julian Stricker <julian@julianstricker.com>
 */

class DefaultBaseController extends Controller
{
    /**
     * Returns a Array of Widgets-Service-Ids
     *
     * @return array
     */
    protected function getRegisteredWidgets(){
        $widgets=Array();
        /** @var Container $container */
        $container=$this->container;
        foreach($container->getServiceIds() as $serviceid){
            if (strpos($serviceid,'xxamportalwidget.')===0){
                $widgets[]=$serviceid;
            }
        }
        return $widgets;
    }

    /**
     * Returns a Array of Menu items
     *
     * @param $config
     * @return array
     */
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

    /**
     * Returns saved Ext-Js Stateful-Settings
     *
     * @param User $user
     * @return Response
     */
    protected function statefulGetResponse(User $user){
        $states=$user->getExtjsstates();
        $resp=Array();
        foreach($states as $state){
            $resp[$state->getStatekey()]=$state->getStatevalue();
        }
        $response = new Response(json_encode($resp));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

    /**
     * Saves new Ext-Js Stateful-Settings
     *
     * @param User $user
     * @param String $key
     * @param mixed $value
     * @return Response
     */
    protected function statefulPostResponse(User $user, $key, $value){
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

    /**
     * Deletes a Ext-Js Stateful-Settings
     *
     * @param User $user
     * @param String $key
     * @return Response
     */
    protected function statefulDeleteResponse(User $user, $key){
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
