<?php
namespace Just\CommBundle\Provider;

use Thruway\Message\ChallengeMessage;
/**
 * Class SimpleClientAuth
 */
class ChattokenClientAuth implements \Thruway\Authentication\ClientAuthenticationInterface
{
    /**
     * Get authentication ID
     * 
     * @return mixed
     */
    public function getAuthId()
    {
        // TODO: Implement getAuthId() method.
        echo "getAuthId:";
    }
    
    /**
     * Set authentication
     * 
     * @param mixed $authid
     */
    public function setAuthId($authid)
    {
        // TODO: Implement setAuthId() method.
        echo "setAuthId:".$authid;
    }
    /**
     * Get list support authentication methods
     * 
     * @return array
     */
    public function getAuthMethods()
    {
        echo 'getAuthMethods()';
        $memcached = new \Memcached;
        $memcached->addServer('localhost', 11211);
        $memcached->add('chatid_imapidlecommand',array(
            'tenant_id'=>0,
            'user_id'>0,
            'username'=>'ImapidleCommand'
        ));
        
        return ["chattoken"];
        // TODO: Implement getAuthMethods() method.
    }
    /**
     * Make Authenticate message from challenge message
     * 
     * @param \Thruway\Message\ChallengeMessage $msg
     * @return \Thruway\Message\AuthenticateMessage
     */
    public function getAuthenticateFromChallenge(ChallengeMessage $msg)
    {
        return new \Thruway\Message\AuthenticateMessage("imapidlecommand");
    }
} 