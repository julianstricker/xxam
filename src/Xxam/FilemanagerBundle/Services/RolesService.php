<?php
namespace Xxam\FilemanagerBundle\Services;

class RolesService
{
    public function getRoledefinitions()
    {
        return ["ROLE_FILEMANAGER_LIST", "ROLE_FILEMANAGER_CREATE", "ROLE_FILEMANAGER_EDIT", "ROLE_FILEMANAGER_DELETE","ROLE_FILEMANAGER_ADMIN","ROLE_FILEMANAGER_ADMIN_GROUP"];

    }
}