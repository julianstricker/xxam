<?php
namespace Xxam\CoreBundle\Services;

class MenuService
{
    public function getMenu() {
        return [
            'coremenu'=>[
                'text'=>       'Portal',
                'iconCls'=>    'x-fa fa-th-large'  ,
                #'handler'=>    'function() { window.location.href="#portal"}',
                'href'=>       '#portal',
                'hrefTarget'=> '_self',
                'stateId'=> 'xxam_menu_portal',
            ]
        ];
    }
}