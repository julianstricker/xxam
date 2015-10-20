<?php

namespace Xxam\CmsAdminBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class XxamCmsAdminBundle extends Bundle {

    static function getMenu() {
        return Array(
            'cmsadminmenu' => Array(
                'text' => 'CMS',
                'iconCls' => 'defaultmenuicon',
                #'handler'=>    'function() { window.location.href="#portal"}',
                'stateId' => 'xxam_menu_cmsadmin',
                'role' => 'ROLE_CMSADMIN_LIST',
                'menu' => Array(
                    'menu_cmsadmin' => Array(
                        'text' => 'CMS',
                        'iconCls' => 'defaultmenuicon',
                        'href' => '#cmsadmin',
                        'role' => 'ROLE_CMSADMIN_LIST'
                    ),
                    'menu_cmsadmin_create' => Array(
                        'text' => 'Create new CMS',
                        'iconCls' => 'defaultmenuicon',
                        'href' => '#cmsadmin',
                        'role' => 'ROLE_CMSADMIN_CREATE'
                    )
                )
            )
        );
    }

    static Function getRoledefinitions() {
        return ["ROLE_CMSADMIN_LIST", "ROLE_CMSADMIN_CREATE", "ROLE_CMSADMIN_EDIT", "ROLE_CMSADMIN_DELETE"];
    }

}
