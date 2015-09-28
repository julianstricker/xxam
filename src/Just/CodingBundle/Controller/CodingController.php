<?php

namespace Just\CodingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Just\CodingBundle\Helper\Imap\ImapMailbox;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class CodingController extends Controller {

    /**
     * Show Coding
     *
     * @Security("has_role('ROLE_CODING_LIST')")
     *
     *
     */
    public function indexAction(Request $request) {
        $path=$request->get('path','');
        return $this->render('JustCodingBundle:Coding:index.js.twig', array('path'=>$path,'id'=>md5($path)));
    }
    
    
}
