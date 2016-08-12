<?php
namespace Xxam\ContactBundle\Services;

class MenuService
{
    public function getMenu() {
        return Array(
            'contactmenu' => Array(
                'text' => 'Contact',
                'iconCls' => 'x-fa fa-users',
                #'handler'=>    'function() { window.location.href="#portal"}',
                'stateId' => 'xxam_menu_contact',
                'role' => 'ROLE_CONTACT_LIST',
                'menu' => Array(
                    'menu_contact' => Array(
                        'text' => 'Contact',
                        'iconCls' => 'x-fa fa-users',
                        'href' => '#contact',
                        'role' => 'ROLE_CONTACT_LIST'
                    ),
                    'menu_contact_create' => Array(
                        'text' => 'Create new Contact',
                        'iconCls' => 'x-fa fa-user-plus',
                        'href' => '#contact',
                        'role' => 'ROLE_CONTACT_CREATE'
                    )
                )
            )
        );
    }
}