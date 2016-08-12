<?php
namespace Xxam\FilemanagerBundle\Services;

class MenuService
{
    public function getMenu() {
        return Array(
            'filemanagermenu' => Array(
                'text' => 'Filemanager',
                'iconCls' => 'x-fa fa-database',
                #'handler'=>    'function() { window.location.href="#portal"}',
                'stateId' => 'xxam_menu_filemanager',
                'role' => 'ROLE_FILEMANAGER_LIST',
                'menu' => Array(
                    'menu_1' => Array(
                        'text' => 'Filemanager',
                        'iconCls' => 'x-fa fa-database',
                        'href' => '#filemanager',
                        'role' => 'ROLE_FILEMANAGER_LIST'
                    )
                )
            ),
            'adminmenu' => Array(
                'text' => 'Administration',
                'iconCls' => 'x-fa fa-database',
                #'handler'=>    'function() { window.location.href="#portal"}',
                'stateId' => 'xxam_menu_admin',
                'role' => 'ROLE_ADMIN',
                'menu' => Array(
                    'menu_filemanager_admin' => Array(
                        'text' => 'Filemanagers',
                        'iconCls' => 'x-fa fa-database',
                        'href' => '#filemanager/admin',
                        'role' => 'ROLE_FILEMANAGER_ADMIN',
                    )
                )
            )
        );
    }
}