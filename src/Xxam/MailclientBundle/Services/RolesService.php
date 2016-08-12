<?php
namespace Xxam\MailclientBundle\Services;

class RolesService
{
    public function getRoledefinitions()
    {
        return ["ROLE_MAILCLIENT_LIST", "ROLE_MAILCLIENT_CREATE", "ROLE_MAILCLIENT_EDIT", "ROLE_MAILCLIENT_DELETE", "ROLE_MAILCLIENT_SETTINGS"];
    }
}