<?php

namespace Xxam\CalendarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Xxam\CalendarBundle\Helper\Imap\ImapMailbox;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class CalendarController extends Controller {

    /**
     * Show Calendar
     *
     * @Security("has_role('ROLE_CALENDAR_LIST')")
     *
     *
     */
    public function indexAction() {
        return $this->render('XxamCalendarBundle:Calendar:index.js.twig', array());
    }
    
    
    
    private function throwJsonError($errormessage) {
        $response = new Response(json_encode(Array('error' => $errormessage)));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }
}
