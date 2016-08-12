<?php
namespace Xxam\UserBundle\Services;

class MenuService
{
    public function getMenu() {
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
}