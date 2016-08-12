<?php
namespace Xxam\CalendarBundle\Services;

class RolesService
{
    public function getRoledefinitions()
    {
        return ["ROLE_CALENDAR_LIST","ROLE_CALENDAR_CREATE","ROLE_CALENDAR_EDIT","ROLE_CALENDAR_DELETE"];
    }
}