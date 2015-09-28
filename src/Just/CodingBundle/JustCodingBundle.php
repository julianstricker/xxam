<?php

namespace Just\CodingBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class JustCodingBundle extends Bundle
{
    static function getMenu() {
        return Array(
            'codingmenu' => Array(
                'text' => 'Code editor',
                'iconCls' => 'defaultmenuicon',
                #'handler'=>    'function() { window.location.href="#portal"}',
                'stateId' => 'xxam_menu_coding',
                'role' => 'ROLE_CODING_LIST',
                'menu' => Array(
                    'menu_1' => Array(
                        'text' => 'Code editor',
                        'iconCls' => 'defaultmenuicon',
                        'href' => '#coding',
                        'role' => 'ROLE_CODING_LIST'
                    )
                )
            )
        );
    }

    static Function getRoledefinitions() {
        return ["ROLE_CODING_LIST", "ROLE_CODING_CREATE", "ROLE_CODING_EDIT", "ROLE_CODING_DELETE"];
    }
}
