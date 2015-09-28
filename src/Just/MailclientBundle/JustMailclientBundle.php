<?php

namespace Just\MailclientBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class JustMailclientBundle extends Bundle {

    static function getMenu() {
        return Array(
            'mailclientmenu' => Array(
                'text' => 'Mailclient',
                'iconCls' => 'defaultmenuicon',
                #'handler'=>    'function() { window.location.href="#portal"}',
                'stateId' => 'xxam_menu_mailclient',
                'role' => 'ROLE_MAILCLIENT_LIST',
                'menu' => Array(
                    'menu_mails' => Array(
                        'text' => 'Mails',
                        'iconCls' => 'defaultmenuicon',
                        'href' => '#mailclient',
                        'role' => 'ROLE_MAILCLIENT_LIST',
                    ),
                    'menu_mail_create' => Array(
                        'text' => 'Create new Message',
                        'iconCls' => 'defaultmenuicon',
                        'href' => '#mailclient/write',
                        'role' => 'ROLE_MAILCLIENT_CREATE',
                    )
                )
            )
        );
    }

    static Function getRoledefinitions() {
        return ["ROLE_MAILCLIENT_LIST", "ROLE_MAILCLIENT_CREATE", "ROLE_MAILCLIENT_EDIT", "ROLE_MAILCLIENT_DELETE", "ROLE_MAILCLIENT_SETTINGS"];
    }

}
