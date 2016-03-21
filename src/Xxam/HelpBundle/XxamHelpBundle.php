<?php

namespace Xxam\HelpBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class XxamHelpBundle extends Bundle {

    static function getMenu() {
        return Array(
            'helpmenu'=>[
                'text' => 'Help',
                'iconCls' => 'x-fa fa-question',
                #'handler'=>    'function() { window.location.href="#portal"}',
                'stateId' => 'xxam_menu_help',
                'menu' => Array(
                    'menu_help_current' => Array(
                        'text' => 'Help for current tab...',
                        'iconCls' => 'x-fa fa-question',
                        'handler'=>    'function(){Xxam.showHelp()}'
                    ),
                    'menu_help_about' => Array(
                        'text' => 'About Xxam...',
                        'iconCls' => 'x-fa fa-cube',
                        'handler'=>    'function(){Xxam.showAbout()}'
                    )
                )
            ]
        );
    }

    static Function getRoledefinitions() {
        return ["ROLE_HELP_LIST", "ROLE_HELP_CREATE", "ROLE_HELP_EDIT", "ROLE_HELP_DELETE"];
    }

}
