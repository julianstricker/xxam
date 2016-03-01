<?php

namespace Xxam\CoreBundle\Services;

class MemcachedService extends \Memcached
{
    private $server_host;
    private $server_port;
    private $options;
    private $persistent_id;

    public function __construct($persistent_id='xxam',$server_host='localhost', $server_port=11211,$options=Array())
    {
        $this->persistent_id = $persistent_id;
        $this->server_host = $server_host;
        $this->server_port = $server_port;
        $this->options = $options;
        parent::__construct($persistent_id);
        $this->addServer($this->server_host,$this->server_port);
        $this->setOptions($this->options);
    }

}