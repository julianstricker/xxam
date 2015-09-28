<?php
namespace Just\CommBundle\Provider;

use Thruway\Peer\Client;

/**
 * Class InternalClient
 */
class XxamInternalClient extends Client
{
    /**
     * Contructor
     */
    public function __construct($realm)
    {
        parent::__construct($realm);
    }
    /**
     * @param \Thruway\ClientSession $session
     * @param \Thruway\Transport\TransportInterface $transport
     */
    public function onSessionStart($session, $transport)
    {
        // TODO: now that the session has started, setup the stuff
        echo "--------------- Hello from InternalClient ------------\n";
        //$session->register('com.xxam.setchattoken', [$this, 'setChattoken']);
        $session->register('com.xxam.getonline',     [$this, 'getOnline']);
        
        $session->subscribe('wamp.metaevent.session.on_join',  [$this, 'onSessionJoin']);
        $session->subscribe('wamp.metaevent.session.on_leave', [$this, 'onSessionLeave']);
    }
   /**
     * Get list online
     * 
     * @return array
     */
    public function getOnline()
    {
        return [$this->_sessions];
    }
    /**
     * Handle on new session joinned
     * 
     * @param array $args
     * @param array $kwArgs
     * @param array $options
     * @return void
     * @link https://github.com/crossbario/crossbar/wiki/Session-Metaevents
     */
    public function onSessionJoin($args, $kwArgs, $options)
    {
        echo "Session  joinned\n";
        $this->_sessions[] = $args[0];
    }
    
    /**
     * Handle on session leaved
     * 
     * @param array $args
     * @param array $kwArgs
     * @param array $options
     * @return void
     * @link https://github.com/crossbario/crossbar/wiki/Session-Metaevents
     */
    public function onSessionLeave($args, $kwArgs, $options)
    {
        dump($args);
        if (!empty($args[0]->session)) {
            foreach ($this->_sessions as $key => $details) {
                if ($args[0]->session == $details->session) {
                    echo "Session {$details->session} leaved\n";
                    unset($this->_sessions[$key]);
                    return;
                }
            }
        }
    }
}