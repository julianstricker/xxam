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
    //const MSG_PUBLISHED = 17;

    const MSG_SUBSCRIBE = 32;
    const MSG_SUBSCRIBED = 33;
    const MSG_UNSUBSCRIBE = 34;
    const MSG_UNSUBSCRIBED = 35;

    const MSG_GETONLINE = 82;
    const MSG_GETONLINE_RESPONSE = 83;

    const MSG_SUBSCRIBEDBROADCAST = 86;
    const MSG_UNSUBSCRIBEDBROADCAST = 87;

    const MSG_SIGNAL = 96;
    //const MSG_SIGNALED = 97;

    const MSG_VIDEOPHONECALL = 101;
    const MSG_VIDEOPHONECALLACCEPT = 102;
    const MSG_VIDEOPHONECALLCANCEL = 103;

    const MSG_DATA = 110;
    const MSG_DATA_ACK = 111;


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
                case XxamChat::MSG_UNSUBSCRIBE:
                    $this->onXxamUnsubscribe($conn,$messagedata);
                    break;
                case XxamChat::MSG_GETONLINE:
                    $this->onXxamGetOnline($conn,$messagedata);
                    break;
                case XxamChat::MSG_PUBLISH:
                    $this->onXxamPublish($conn,$messagedata);
                    break;
                case XxamChat::MSG_SIGNAL:
                    $this->onXxamSignal($conn,$messagedata);
                    break;
                case XxamChat::MSG_VIDEOPHONECALL:
                    $this->onXxamVideophonecall($conn,$messagedata);
                    break;
                case XxamChat::MSG_VIDEOPHONECALLACCEPT:
                    $this->onXxamVideophonecallaccept($conn,$messagedata);
                    break;
                case XxamChat::MSG_VIDEOPHONECALLCANCEL:
                    $this->onXxamVideophonecallcancel($conn,$messagedata);
                    break;
                case XxamChat::MSG_DATA:
                    $this->onXxamData($conn,$messagedata);
                    break;
                case XxamChat::MSG_DATA_ACK:
                    $this->onXxamDataAck($conn,$messagedata);
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
                $this->doUnsubscribe( $conn, $subscription);

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


    private function getUsersForTopic($sessionid, $topic){
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


    /**
     * Broadcast message to all users of a topic, except $conn
     *
     * @param ConnectionInterface $conn
     * @param string $topic
     * @param string $messagestring
     * @return bool
     */
    private function broadcastToTopic(ConnectionInterface $conn, $topic, $messagestring){
        $user=$this->getUser($conn->resourceId);
        if (!$user) return false;

        foreach ($this->clients as $client) {
            if ($conn !== $client) {
                if(in_array($client->resourceId,$this->subscriptionusers[$topic])) {
                    $userdata = $this->memcached->get('chatsessionid_' . $client->resourceId);
                    if ($user['tenant_id'] == null || $userdata['tenant_id'] == null || $user['tenant_id'] == $userdata['tenant_id']) {
                        $client->send($messagestring);
                    }
                }
            }
        }
    }


    private function onXxamGetOnline(ConnectionInterface $conn, \stdClass $messagedata){
        $topic=$messagedata->topic;
        $returnval=$this->getUsersForTopic($conn->resourceId,$topic);
        echo 'onXxamGetOnline';
        $conn->send(json_encode([XxamChat::MSG_GETONLINE_RESPONSE,[$topic=>$returnval],'']));
    }

    private function onXxamSubscribe(ConnectionInterface $conn, \stdClass $messagedata){
        if (!isset($this->usersubscriptions[$conn->resourceId])) $this->usersubscriptions[$conn->resourceId]=[];
        if(!in_array($messagedata->topic,$this->usersubscriptions[$conn->resourceId])) $this->usersubscriptions[$conn->resourceId][]=$messagedata->topic;
        if(!isset($this->subscriptionusers[$messagedata->topic])){
            $this->subscriptionusers[$messagedata->topic]=[];
        }
        if(!in_array($conn->resourceId,$this->subscriptionusers[$messagedata->topic])){
            $this->subscriptionusers[$messagedata->topic][]=$conn->resourceId;
        }
        $conn->send(json_encode([XxamChat::MSG_SUBSCRIBED,["topic"=>$messagedata->topic],'']));
        $user = $this->memcached->get('chatsessionid_' . $conn->resourceId);
        $this->broadcastToTopic($conn, $messagedata->topic, json_encode([XxamChat::MSG_SUBSCRIBEDBROADCAST, [$messagedata->topic=>[$conn->resourceId=>$user['username']]],'']));

    }

    private function onXxamUnsubscribe(ConnectionInterface $conn, \stdClass $messagedata){
        return $this->doUnsubscribe($conn,$messagedata->topic);
    }

    private function doUnsubscribe(ConnectionInterface $conn, $topic){
        if(!in_array($topic,$this->usersubscriptions[$conn->resourceId])) return false;
        if(!isset($this->subscriptionusers[$topic])) return false;

        $arrpos=array_search($conn->resourceId,$this->subscriptionusers[$topic]);
        if($arrpos!==false){
            unset($this->subscriptionusers[$topic][$arrpos]);
        }
        $arrpos=array_search($topic,$this->usersubscriptions[$conn->resourceId]);
        if($arrpos!==false){
            unset($this->usersubscriptions[$conn->resourceId][$arrpos]);
        }
        $conn->send(json_encode([XxamChat::MSG_UNSUBSCRIBED,["topic"=>$topic],'']));
        $user = $this->memcached->get('chatsessionid_' . $conn->resourceId);
        $this->broadcastToTopic($conn, $topic, json_encode([XxamChat::MSG_UNSUBSCRIBEDBROADCAST, [$topic=>[$conn->resourceId=>$user['username']]],'']));
        return true;
    }

    private function onXxamPublish(ConnectionInterface $conn, \stdClass $messagedata){
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
                    $client->send(json_encode([XxamChat::MSG_PUBLISH,["topic"=>$topic,"message"=>$message],$conn->resourceId]));
            }
        }

    }
    private function onXxamSignal(ConnectionInterface $conn, \stdClass $messagedata){
        $user=$this->getUser($conn->resourceId);
        $receivers=$messagedata->receivers;
        $data=$messagedata->data;

        dump($data);

        foreach ($this->clients as $client) {
            if ($conn !== $client &&  (count($receivers>0) && in_array($client->resourceId,$receivers))) {
                // The sender is not the receiver, send to each client connected
                $userdata=$this->memcached->get('chatsessionid_'.$client->resourceId);
                if($user['tenant_id']==null || $userdata['tenant_id']==null || $user['tenant_id']==$userdata['tenant_id'])
                    $client->send(json_encode([XxamChat::MSG_SIGNAL,["data"=>$data],$conn->resourceId]));
            }
        }
    }
    private function onXxamVideophonecall(ConnectionInterface $conn, \stdClass $messagedata){
        $user=$this->getUser($conn->resourceId);
        $receivers=$messagedata->receivers;
        foreach ($this->clients as $client) {
            if ($conn !== $client &&  (count($receivers>0) && in_array($client->resourceId,$receivers))) {
                // The sender is not the receiver, send to each client connected
                $userdata=$this->memcached->get('chatsessionid_'.$client->resourceId);
                if($user['tenant_id']==null || $userdata['tenant_id']==null || $user['tenant_id']==$userdata['tenant_id'])
                    $client->send(json_encode([XxamChat::MSG_VIDEOPHONECALL,[],$conn->resourceId]));
            }
        }
    }
    private function onXxamVideophonecallaccept(ConnectionInterface $conn, \stdClass $messagedata){
        $user=$this->getUser($conn->resourceId);
        $receivers=$messagedata->receivers;
        foreach ($this->clients as $client) {
            if ($conn !== $client &&  (count($receivers>0) && in_array($client->resourceId,$receivers))) {
                // The sender is not the receiver, send to each client connected
                $userdata=$this->memcached->get('chatsessionid_'.$client->resourceId);
                if($user['tenant_id']==null || $userdata['tenant_id']==null || $user['tenant_id']==$userdata['tenant_id'])
                    $client->send(json_encode([XxamChat::MSG_VIDEOPHONECALLACCEPT,[],$conn->resourceId]));
            }
        }
    }
    private function onXxamVideophonecallcancel(ConnectionInterface $conn, \stdClass $messagedata){
        $user=$this->getUser($conn->resourceId);
        $receivers=$messagedata->receivers;
        foreach ($this->clients as $client) {
            if ($conn !== $client &&  (count($receivers>0) && in_array($client->resourceId,$receivers))) {
                // The sender is not the receiver, send to each client connected
                $userdata=$this->memcached->get('chatsessionid_'.$client->resourceId);
                if($user['tenant_id']==null || $userdata['tenant_id']==null || $user['tenant_id']==$userdata['tenant_id'])
                    $client->send(json_encode([XxamChat::MSG_VIDEOPHONECALLCANCEL,[],$conn->resourceId]));
            }
        }
    }

    private function onXxamData(ConnectionInterface $conn, \stdClass $messagedata){
        $user=$this->getUser($conn->resourceId);
        $receivers=$messagedata->receivers;
        $data=$messagedata->data;
        foreach ($this->clients as $client) {
            if ($conn !== $client &&  (count($receivers>0) && in_array($client->resourceId,$receivers))) {
                // The sender is not the receiver, send to each client connected
                $userdata=$this->memcached->get('chatsessionid_'.$client->resourceId);
                if($user['tenant_id']==null || $userdata['tenant_id']==null || $user['tenant_id']==$userdata['tenant_id'])
                    $client->send(json_encode([XxamChat::MSG_DATA,["data"=>$data],$conn->resourceId]));
            }
        }
    }

    private function onXxamDataAck(ConnectionInterface $conn, \stdClass $messagedata){
        $user=$this->getUser($conn->resourceId);
        $receivers=$messagedata->receivers;
        $data=$messagedata->data;
        foreach ($this->clients as $client) {
            if ($conn !== $client &&  (count($receivers>0) && in_array($client->resourceId,$receivers))) {
                // The sender is not the receiver, send to each client connected
                $userdata=$this->memcached->get('chatsessionid_'.$client->resourceId);
                if($user['tenant_id']==null || $userdata['tenant_id']==null || $user['tenant_id']==$userdata['tenant_id'])
                    $client->send(json_encode([XxamChat::MSG_DATA_ACK,["data"=>$data],$conn->resourceId]));
            }
        }
    }
}