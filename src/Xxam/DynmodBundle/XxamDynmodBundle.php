<?php

namespace Xxam\DynmodBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class XxamDynmodBundle extends Bundle {

    static function getMenu() {
        return Array(
            'dynmodmenu' => Array(
                'text' => 'Dynmod',
                'iconCls' => 'x-fa fa-cubes',
                #'handler'=>    'function() { window.location.href="#portal"}',
                'stateId' => 'xxam_menu_dynmod',
                'role' => 'ROLE_DYNMOD_LIST',
                'menu' => Array(
                    'menu_dynmod' => Array(
                        'text' => 'Dynmod',
                        'iconCls' => 'x-fa fa-cubes',
                        'href' => '#dynmod',
                        'role' => 'ROLE_DYNMOD_LIST'
                    ),
                    'menu_dynmod_create' => Array(
                        'text' => 'Create new Dynmod',
                        'iconCls' => 'x-fa fa-cube',
                        'href' => '#dynmod',
                        'role' => 'ROLE_DYNMOD_CREATE'
                    )
                )
            )
        );
    }

    static Function getRoledefinitions() {
        return ["ROLE_DYNMOD_LIST", "ROLE_DYNMOD_CREATE", "ROLE_DYNMOD_EDIT", "ROLE_DYNMOD_DELETE"];
    }

}
