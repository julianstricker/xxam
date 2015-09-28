<?php

namespace Just\AdminBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class JustAdminBundle extends Bundle
{
    static function getMenu(){
        return Array('hauptmenu_1'=>Array(
            'text'=>       'Portal',
            'iconCls'=>    'defaultmenuicon'  ,          
            #'handler'=>    'function() { window.location.href="#portal"}',
            'href'=>       '#portal',
            'hrefTarget'=> '_self',
            'stateId'=> 'xxam_menu_portal',
            )
        );
    }
    static Function getRoledefinitions(){
        return ["ROLE_ADMIN","ROLE_USER"];
    }
}
