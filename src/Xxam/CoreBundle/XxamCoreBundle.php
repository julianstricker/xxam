<?php

/*
 * This file is part of the Xxam package.
 *
 * (c) Julian Stricker <julian@julianstricker.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xxam\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class XxamCoreBundle extends Bundle
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
