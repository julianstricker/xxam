<?php
namespace Xxam\DynmodBundle\Services;

class RolesService
{
    public function getRoledefinitions()
    {
        return ["ROLE_DYNMOD_LIST", "ROLE_DYNMOD_CREATE", "ROLE_DYNMOD_EDIT", "ROLE_DYNMOD_DELETE"];
    }
}