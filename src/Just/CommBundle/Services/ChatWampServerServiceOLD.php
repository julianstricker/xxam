<?php

namespace Just\CommBundle\Services;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class ChatWampServerService implements WampServerInterface
{
    protected $clients;
    protected $container;
    protected $tenant_id;
    protected $rooms;
    public function __construct($container) {
        //parent::__construct();
        $this->clients = new \SplObjectStorage;
        $this->container=$container;
        $this->tenants=$container->getParameter('tenants');
        dump($this->tenants);
        $this->rooms=Array();
        $this->m=new \Memcached();
        $this->m->addServer('localhost', 11211);
        //dump($this->m->getAllKeys());
        
    }
    
    public function onOpen(ConnectionInterface $conn)
    {
        $conn->Chat        = new \StdClass;
        $conn->Chat->rooms = array();
        $this->clients->attach($conn);
        //dump( $conn->WAMP);
        
        echo "New connection! ({$conn->resourceId}) - {$conn->WAMP->sessionId}\n";
    }

    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        //dump($conn->WAMP->subscriptions);
        $user=$this->m->get('chatid_'.$conn->WAMP->sessionId);
        foreach($conn->WAMP->subscriptions as $topic){
            $this->onUnSubscribe($conn, $topic);
            if($user) $this->broadcast($conn, $topic, Array('message'=>$user['username'].' has disconnected.'));
        }
        $this->m->delete('chatid_'.$conn->WAMP->sessionId);
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
        
    }

    public function onSubscribe(ConnectionInterface $conn, $topic)
    {
        $user=$this->m->get('chatid_'.$conn->WAMP->sessionId);
        echo "\nroom:";
        dump($conn->Chat->rooms);
        if (!$user){
            $conn->close();
        }else{
            dump($user);
            $conn->Chat->rooms[$topic->__toString()]=$topic;
            $this->broadcast($conn, $topic, Array('message'=>$user['username'].' connected.'));
        }
    }

    public function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
        echo "Connection {$conn->resourceId} has unsubscribed\n";
        $user=$this->m->get('chatid_'.$conn->WAMP->sessionId);
        if (!$user){
            $conn->close();
        }else{
            $this->broadcast($conn, $topic, Array('message'=>$user['username'].' has unsubscribed.'));
        }
        
    }

    public function onCall(ConnectionInterface $conn, $id, $topic, array $params)
    {
        dump($topic->__toString());
        dump($id);
        dump($params);
        dump($topic);
        dump($topic->__toString());
        $user=$this->m->get('chatid_'.$conn->WAMP->sessionId);
        if (!$user){
            $conn->close();
        }else{
            $result=Array();
            switch ($topic->__toString()) {
                case 'getUserlist':
                    $userlist=Array();
                    dump($topic->getIterator());
                    foreach($this->clients as $client){
                        echo "\nSessid: ".$client->WAMP->sessionId;
                        $ruser=$this->m->get('chatid_'.$client->WAMP->sessionId);
                        if (isset($client->Chat->rooms[$params['room']])){
                            if ($ruser && ($user['tenant_id']==null || $ruser['tenant_id']==null || $user['tenant_id']==$ruser['tenant_id'])){
                                $userlist[$client->WAMP->sessionId]=$ruser['username'];
                            }
                        }
                    }
                    $conn->callResult($id,array('userlist'=>$userlist));
                    break;
                case 'createRoom':
                    $conn->callResult($id,array('jojo'=>'woascheh'));
                default:
                    return $conn->callError($id, 'Unknown call');
                    break;
            }
        }
    }

    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        $this->broadcast($conn, $topic, $event, $exclude, $eligible);
        
    }
    
    private function broadcast(ConnectionInterface $conn, $topic, $event, array $exclude=Array(), array $eligible=Array()){
        $user=$this->m->get('chatid_'.$conn->WAMP->sessionId);
        if (!$user){
            $conn->close();
        }else{
            $neligible=Array();
            foreach($topic->getIterator() as $client){
                echo "\n".$client->WAMP->sessionId." - > ".$conn->WAMP->sessionId;
                $ruser=$this->m->get('chatid_'.$client->WAMP->sessionId);
                if (($user['tenant_id']==null || $ruser['tenant_id']==null || $user['tenant_id']==$ruser['tenant_id']) &&(count($eligible)==0 || (count($eligible)>0 && in_array($client->WAMP->sessionId,$eligible))) ){
                    $neligible[]=$client->WAMP->sessionId;
                }
            }
            dump($neligible);
            if (count($neligible)>0) $topic->broadcast($event,$exclude,$neligible);
        }
        
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
         echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

}