<?php
namespace Xxam\CalendarBundle\Services;

class MenuService
{
    public function getMenu(){
        return Array(
            'calendarmenu'=>Array(
                'text'=>       'Calendar',
                'iconCls'=>    'x-fa fa-calendar'  ,
                #'handler'=>    'function() { window.location.href="#portal"}',
                'stateId'=> 'xxam_menu_calendar',
                'role' => 'ROLE_CALENDAR_LIST',
                'menu' => Array(
                    'menu_calendar'=>Array(
                        'text'=>       'Calendar',
                        'iconCls'=>    'x-fa fa-calendar'  ,
                        'href'=>       '#calendar',
                        'role' => 'ROLE_CALENDAR_LIST'
                    )
                )
            )
        );
    }
}