<?php

namespace Xxam\FilemanagerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class XxamFilemanagerBundle extends Bundle {

    static function getMenu() {
        return Array(
            'filemanagermenu' => Array(
                'text' => 'Filemanager',
                'iconCls' => 'defaultmenuicon',
                #'handler'=>    'function() { window.location.href="#portal"}',
                'stateId' => 'xxam_menu_filemanager',
                'role' => 'ROLE_FILEMANAGER_LIST',
                'menu' => Array(
                    'menu_1' => Array(
                        'text' => 'Filemanager',
                        'iconCls' => 'defaultmenuicon',
                        'href' => '#filemanager',
                        'role' => 'ROLE_FILEMANAGER_LIST'
                    )
                )
            ),
            'adminmenu' => Array(
                'text' => 'Administration',
                'iconCls' => 'defaultmenuicon',
                #'handler'=>    'function() { window.location.href="#portal"}',
                'stateId' => 'xxam_menu_admin',
                'role' => 'ROLE_ADMIN',
                'menu' => Array(
                    'menu_filemanager_admin' => Array(
                        'text' => 'Filemanagers',
                        'iconCls' => 'defaultmenuicon',
                        'href' => '#filemanager/admin',
                        'role' => 'ROLE_FILEMANAGER_ADMIN',
                    )
                )
            )
        );
    }

    static Function getRoledefinitions() {
        return ["ROLE_FILEMANAGER_LIST", "ROLE_FILEMANAGER_CREATE", "ROLE_FILEMANAGER_EDIT", "ROLE_FILEMANAGER_DELETE","ROLE_FILEMANAGER_ADMIN","ROLE_FILEMANAGER_ADMIN_GROUP"];
    }

}
