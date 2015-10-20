<?php
namespace Xxam\CommBundle\Provider;

use Thruway\Authentication\AbstractAuthProviderClient;

class ChattokenAuthenticationProvider extends AbstractAuthProviderClient {
    private $key;

    public function __construct(Array $authRealms, $key) {
        $this->key = $key;
        dump($authRealms);
        parent::__construct($authRealms);
    }

    public function getMethodName() {
        return 'chattoken';
    }

    public function processAuthenticate($chattoken, $extra = null)
    {
        error_reporting(E_ALL);
        dump($chattoken);
        dump($extra);
        dump($this->getAuthId());
        $memcached=new \Memcached();
        $memcached->addServer('localhost', 11211);
        $userdata=$memcached->get('chatid_'.$chattoken);
        if (isset($userdata['username'])) {
            return ["SUCCESS", ["authid" => $userdata['username']]];
        } else {
            return ["FAILURE"];
        }
    }
}