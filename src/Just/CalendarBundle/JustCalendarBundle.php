<?php

namespace Just\CalendarBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class JustCalendarBundle extends Bundle
{
    static function getMenu(){
        return Array(
            'calendarmenu'=>Array(
                'text'=>       'Calendar',
                'iconCls'=>    'defaultmenuicon'  ,          
                #'handler'=>    'function() { window.location.href="#portal"}',
                'stateId'=> 'xxam_menu_calendar',
                'role' => 'ROLE_CALENDAR_LIST',
                'menu' => Array(
                    'menu_calendar'=>Array(
                        'text'=>       'Calendar',
                        'iconCls'=>    'defaultmenuicon'  ,          
                        'href'=>       '#calendar',
                        'role' => 'ROLE_CALENDAR_LIST'
                    )
                )
             )
        );
    }
    
    static Function getRoledefinitions(){
        return ["ROLE_CALENDAR_LIST","ROLE_CALENDAR_CREATE","ROLE_CALENDAR_EDIT","ROLE_CALENDAR_DELETE"];
    }
}
