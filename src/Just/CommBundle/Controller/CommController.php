<?php

namespace Just\CommBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class CommController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('JustCommBundle:Default:index.html.twig', array('name' => $name));
    }
    
    
    private function getchatuser(){
        $memcached = new \Memcached;
        $memcached->addServer('localhost', 11211);
        $details    = $this->get('thruway.details');
        $sessionid=(string)$details->getDetails()->caller;
        $user=$memcached->get('chatid_'.$sessionid);
        return $user;
    }
    
    public function setchatidAction(Request $request){
        $sessionid=$request->get('sessionid');
        $memcached = new \Memcached;
        $memcached->addServer('localhost', 11211);
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        $memcached->add('chatsessionid_'.$sessionid,array(
            'tenant_id'=>$user->getTenantId(),
            'user_id'>$user->getId(),
            'username'=>$user->getUsername()
        ));
        
        $response = new Response(json_encode(Array('status'=>'OK')));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response; 
    }
}
