<?php
namespace Xxam\CommBundle\Provider;

/**
 * Created by PhpStorm.
 * User: julianstricker
 * Date: 04.11.15
 * Time: 19:55
 */

use Ratchet\ConnectionInterface;

/**
 * When a user publishes to a topic all clients who have subscribed
 * to that topic will receive the message/event from the publisher
 */
class XxamChat implements \Ratchet\MessageComponentInterface  {
    protected $clients;

    protected $subscriptionusers=[];
    protected $usersubscriptions=[];

    /*
     * Message code
     * @const int
     */
    const MSG_UNKNOWN = 0;
    //const MSG_HELLO = 1;
    const MSG_WELCOME = 2;  //Server -> client, data must contain "id"

    const MSG_ERROR = 8;

    const MSG_PUBLISH = 16;
    const MSG_PUBLISHED = 17;

    const MSG_SUBSCRIBE = 32;
    const MSG_SUBSCRIBED = 33;
    const MSG_UNSUBSCRIBE = 34;
    const MSG_UNSUBSCRIBED = 35;

    const MSG_GETONLINE = 82;
    const MSG_GETONLINE_RESPONSE = 83;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->memcached=new \Memcached();
        $this->memcached->addServer('localhost', 11211);
        foreach($this->memcached->getAllKeys() as $key){
            if (substr($key,0,14)=='chatsessionid_'){
                //echo "\n$key";
                $this->memcached->delete($key);
            }
        }
        //dump($this->memcached->getAllKeys());
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
        //send welcome message:
        $msg=[XxamChat::MSG_WELCOME,['id'=>$conn->resourceId],''];
        $conn->send(json_encode($msg));
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $conn, $msg) {
        echo sprintf('Got message from %d: "%s"' . "\n" , $conn->resourceId, $msg);
        $user=$this->getUser($conn->resourceId);
        if (!$user){
            $conn->send($this->errorMsg('not allowed'));
            $this->clients->detach($conn);
            $conn->close();
            echo "\n".'user not allowed';
        }else{
            $message=json_decode($msg);
            $messagetype=$message[0];
            $messagedata=$message[1];
            switch($messagetype){
                case XxamChat::MSG_SUBSCRIBE:
                    $this->onXxamSubscribe($conn,$messagedata);
                    break;
                case XxamChat::MSG_GETONLINE:
                    $this->onXxamGetOnline($conn,$messagedata);
                    break;
                case XxamChat::MSG_PUBLISH:
                    $this->onXxamPublish($conn,$messagedata);
                    break;
            }
            /*foreach ($this->clients as $client) {
                if ($conn !== $client) {
                    // The sender is not the receiver, send to each client connected
                    $client->send($msg);
                }
            }*/
        }


    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
        if (isset($this->usersubscriptions[$conn->resourceId])){
            foreach ($this->usersubscriptions[$conn->resourceId] as $subscription){
                $key=array_search($conn->resourceId,$this->subscriptionusers[$subscription]);
                if (false!==$key){
                    unset($this->subscriptionusers[$subscription][$key]);
                }

            }
            unset($this->usersubscriptions[$conn->resourceId]);
        }

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    private function getUser($sessionid){
        //echo "\nsessionid:".$sessionid;
        $userdata=$this->memcached->get('chatsessionid_'.$sessionid);
        //dump($userdata);
        if (isset($userdata['username'])) {
            return $userdata;
        }else{
            return false;
        }
    }
    private function errorMsg($message){
        return json_encode([XxamChat::MSG_ERROR,$message,'']);
    }

    private function getUsersForTopic($sessionid,$topic){
        $user=$this->getUser($sessionid);
        $returnval=[];
        if (isset($this->subscriptionusers[$topic])){
            foreach($this->subscriptionusers[$topic] as $sessionid){
                $userdata=$this->memcached->get('chatsessionid_'.$sessionid);
                if($user['tenant_id']==null || $userdata['tenant_id']==null || $user['tenant_id']==$userdata['tenant_id']) $returnval[$userdata['sessionid']]=$userdata['username'];
            }
        }
        return $returnval;
    }


    private function onXxamGetOnline($conn,$messagedata){
        $topic=$messagedata->topic;
        $returnval=$this->getUsersForTopic($conn->resourceId,$topic);
        $conn->send(json_encode([XxamChat::MSG_GETONLINE_RESPONSE,["online"=>[$topic=>$returnval]],'']));
    }

    private function onXxamSubscribe($conn,$messagedata){
        if (!isset($this->usersubscriptions[$conn->resourceId])) $this->usersubscriptions[$conn->resourceId]=[];
        if(!in_array($messagedata->topic,$this->usersubscriptions[$conn->resourceId])) $this->usersubscriptions[$conn->resourceId][]=$messagedata->topic;
        if(!isset($this->subscriptionusers[$messagedata->topic])){
            $this->subscriptionusers[$messagedata->topic]=[];
        }
        if(!in_array($conn->resourceId,$this->subscriptionusers[$messagedata->topic])){
            $this->subscriptionusers[$messagedata->topic][]=$conn->resourceId;
        }
        $conn->send(json_encode([XxamChat::MSG_SUBSCRIBED,["topic"=>$messagedata->topic],'']));
    }
    private function onXxamPublish($conn,$messagedata){
        $user=$this->getUser($conn->resourceId);
        $receivers=$messagedata->receivers;
        $message=$messagedata->message;
        $topic=property_exists($messagedata, 'topic') ? $messagedata->topic : '';
        dump($messagedata);
        if ($topic!=''){
            $users=$this->getUsersForTopic($conn->resourceId,$topic);
        }
        foreach ($this->clients as $client) {
            if ($conn !== $client && (($topic!='' && isset($users[$client->resourceId])) || (count($receivers>0) && in_array($client->resourceId,$receivers)))) {
                // The sender is not the receiver, send to each client connected
                $userdata=$this->memcached->get('chatsessionid_'.$client->resourceId);
                if($user['tenant_id']==null || $userdata['tenant_id']==null || $user['tenant_id']==$userdata['tenant_id'])
                    $client->send(json_encode([XxamChat::MSG_PUBLISHED,["topic"=>$topic,"message"=>$message],$conn->resourceId]));
            }
        }

    }
}