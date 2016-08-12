<?php
namespace Xxam\CodingBundle\Services;

class RolesService
{
    public function getRoledefinitions()
    {
        return ["ROLE_CODING_LIST", "ROLE_CODING_CREATE", "ROLE_CODING_EDIT", "ROLE_CODING_DELETE"];
    }
}