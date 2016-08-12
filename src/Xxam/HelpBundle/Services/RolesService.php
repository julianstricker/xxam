<?php
namespace Xxam\HelpBundle\Services;

class RolesService
{
    public function getRoledefinitions()
    {
        return ["ROLE_HELP_LIST", "ROLE_HELP_CREATE", "ROLE_HELP_EDIT", "ROLE_HELP_DELETE"];
    }
}