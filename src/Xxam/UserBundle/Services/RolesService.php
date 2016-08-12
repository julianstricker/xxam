<?php
namespace Xxam\UserBundle\Services;

class RolesService
{
    public function getRoledefinitions()
    {
        return ["ROLE_USER_LIST", "ROLE_USER_CREATE", "ROLE_USER_EDIT", "ROLE_USER_DELETE", "ROLE_GROUP_LIST", "ROLE_GROUP_CREATE", "ROLE_GROUP_EDIT", "ROLE_GROUP_DELETE"];

    }
}