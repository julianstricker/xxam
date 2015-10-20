<?php

namespace Xxam\CoreBundle\Services;

class MenuService
{
    private $menu;

    /**
     * @param Array $menu
     */
    public function __construct($menu=Array())
    {
        $this->menu = $menu;
        $registeredwidgets=$this->getRegisteredWidgets();
        foreach($this->container->getParameter('kernel.bundles') as $bundle){
            
        }
    }
    protected function getMenuItems($items){
	$menu=Array();
	foreach($items as $value){
            $menuitem=Array();
            foreach($value as $key=>$val){
                if($key=='menu') {
                    $menuitem[$key]=$this->getMenuItems($val);
                }else{
                    $menuitem[$key]=$val;
                }
            }
	    
	    $menu[]=$menuitem;
        }
	return $menu;
    }
    public function getMenu()
    {
        return $this->getMenuItems($this->menu);
    }
    
    
    public function addMenu($menu)
    {
        $this->menu = array_merge_recursive($this->menu, $menu);
    }
}