<?php
namespace Xxam\CodingBundle\Services;

class MenuService
{
    public function getMenu() {
        return Array(
            'codingmenu' => Array(
                'text' => 'Code editor',
                'iconCls' => 'x-fa fa-edit',
                #'handler'=>    'function() { window.location.href="#portal"}',
                'stateId' => 'xxam_menu_coding',
                'role' => 'ROLE_CODING_LIST',
                'menu' => Array(
                    'menu_1' => Array(
                        'text' => 'Code editor',
                        'iconCls' => 'x-fa fa-edit',
                        'href' => '#coding',
                        'role' => 'ROLE_CODING_LIST'
                    )
                )
            )
        );
    }
}