<?php

namespace Just\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class DefaultBaseController extends Controller
{
    
    
    protected function getRegisteredWidgets(){
        $widgets=Array();
        foreach($this->container->getServiceIds() as $serviceid){
            if (strpos($serviceid,'justportalwidget.')===0){
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
    
    
}
