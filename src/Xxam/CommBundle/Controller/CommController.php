<?php

namespace Xxam\CommBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class CommController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('XxamCommBundle:Default:index.html.twig', array('name' => $name));
    }

    public function setchatidAction(Request $request){
        $sessionid=$request->get('sessionid');
        $memcached = new \Memcached;
        $memcached->addServer('localhost', 11211);
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if (!$user){
            $response = new Response(json_encode(Array('status'=>'ERROR')));
            $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
            return $response;
        }
        $memcached->add('chatsessionid_'.$sessionid,array(
            'tenant_id'=>$user->getTenantId(),
            'user_id'=>$user->getId(),
            'username'=>$user->getUsername(),
            'sessionid'=>$sessionid
        ));

        $response = new Response(json_encode(Array('status'=>'OK')));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response; 
    }
}
