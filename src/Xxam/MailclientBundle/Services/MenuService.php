<?php
namespace Xxam\MailclientBundle\Services;

class MenuService
{
    public function getMenu() {
        return Array(
            'mailclientmenu' => Array(
                'text' => 'Mailclient',
                'iconCls' => 'x-fa fa-envelope',
                #'handler'=>    'function() { window.location.href="#portal"}',
                'stateId' => 'xxam_menu_mailclient',
                'role' => 'ROLE_MAILCLIENT_LIST',
                'menu' => Array(
                    'menu_mails' => Array(
                        'text' => 'Mails',
                        'iconCls' => 'x-fa fa-envelope',
                        'href' => '#mailclient',
                        'role' => 'ROLE_MAILCLIENT_LIST',
                    ),
                    'menu_mail_create' => Array(
                        'text' => 'Create new Message',
                        'iconCls' => 'x-fa fa-envelope',
                        'href' => '#mailclient/write',
                        'role' => 'ROLE_MAILCLIENT_CREATE',
                    )
                )
            )
        );
    }
}