<?php

namespace Just\ContactBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class JustContactBundle extends Bundle {

    static function getMenu() {
        return Array(
            'contactmenu' => Array(
                'text' => 'Contact',
                'iconCls' => 'defaultmenuicon',
                #'handler'=>    'function() { window.location.href="#portal"}',
                'stateId' => 'xxam_menu_contact',
                'role' => 'ROLE_CONTACT_LIST',
                'menu' => Array(
                    'menu_contact' => Array(
                        'text' => 'Contact',
                        'iconCls' => 'defaultmenuicon',
                        'href' => '#contact',
                        'role' => 'ROLE_CONTACT_LIST'
                    ),
                    'menu_contact_create' => Array(
                        'text' => 'Create new Contact',
                        'iconCls' => 'defaultmenuicon',
                        'href' => '#contact',
                        'role' => 'ROLE_CONTACT_CREATE'
                    )
                )
            )
        );
    }

    static Function getRoledefinitions() {
        return ["ROLE_CONTACT_LIST", "ROLE_CONTACT_CREATE", "ROLE_CONTACT_EDIT", "ROLE_CONTACT_DELETE"];
    }

}
