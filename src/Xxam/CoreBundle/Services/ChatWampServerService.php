<?php

namespace Xxam\CoreBundle\Services;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class ChatWampServerService implements WampServerInterface
{
    protected $clients;

    public function __construct() {
        parent::__construct();
        $this->clients = new \SplObjectStorage;
        
        $this->m=new \Memcached();
        $this->m->addServer('localhost', 11211);
    }
    
    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onSubscribe(ConnectionInterface $conn, $topic)
    {
    }

    public function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
    }

    public function onCall(ConnectionInterface $conn, $id, $topic, array $params)
    {
    }

    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        dump($topic->__toString());
        dump($event);
        $topic->broadcast($event);
        
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
         echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

}