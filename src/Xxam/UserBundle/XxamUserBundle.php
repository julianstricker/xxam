<?php

namespace Xxam\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class XxamUserBundle extends Bundle {

    static function getMenu() {
        return Array(
            'adminmenu' => Array(
                'text' => 'Administration',
                'iconCls' => 'x-fa fa-cogs',
                #'handler'=>    'function() { window.location.href="#portal"}',
                'stateId' => 'xxam_menu_admin',
                'role' => 'ROLE_ADMIN',
                'menu' => Array(
                    'menu_user' => Array(
                        'text' => 'Users',
                        'iconCls' => 'x-fa fa-user',
                        'href' => '#user',
                        'role' => 'ROLE_USER_LIST',
                    ),
                    'menu_user_create' => Array(
                        'text' => 'Create new User',
                        'iconCls' => 'x-fa fa-user-plus',
                        'href' => '#user/edit',
                        'role' => 'ROLE_USER_CREATE'
                    ),
                    'menu_group' => Array(
                        'text' => 'Groups',
                        'iconCls' => 'x-fa fa-users',
                        'href' => '#group',
                        'role' => 'ROLE_GROUP_LIST'
                    ),
                    'menu_group_create' => Array(
                        'text' => 'Create new Group',
                        'iconCls' => 'x-fa fa-users',
                        'href' => '#group/edit',
                        'role' => 'ROLE_GROUP_EDIT'
                    )
                )
            )
        );
    }

    static Function getRoledefinitions() {
        return ["ROLE_USER_LIST", "ROLE_USER_CREATE", "ROLE_USER_EDIT", "ROLE_USER_DELETE", "ROLE_GROUP_LIST", "ROLE_GROUP_CREATE", "ROLE_GROUP_EDIT", "ROLE_GROUP_DELETE"];
    }

    public function getParent() {
        return 'FOSUserBundle';
    }

}
