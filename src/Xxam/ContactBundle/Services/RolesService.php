<?php
namespace Xxam\ContactBundle\Services;

class RolesService
{
    public function getRoledefinitions()
    {
        return ["ROLE_CONTACT_LIST", "ROLE_CONTACT_CREATE", "ROLE_CONTACT_EDIT", "ROLE_CONTACT_DELETE"];
    }
}