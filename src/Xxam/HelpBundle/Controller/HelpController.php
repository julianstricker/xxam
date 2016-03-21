<?php

namespace Xxam\HelpBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Xxam\CoreBundle\Entity\LogEntryRepository;
use Xxam\HelpBundle\Entity\Help;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Xxam\HelpBundle\Entity\HelpRepository;

/**
 * Help controller.
 *
 * @Route("/help")
 */
class HelpController extends Controller
{


    
    
    /**
     * Lists all Help entities.
     *
     * @Route("", name="help")
     * @Method("GET")
     * @Security("has_role('ROLE_HELP_LIST')")
     */
    public function indexAction() {
        /** @var HelpRepository $repository */
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamHelpBundle:Help');
        return $this->render('XxamHelpBundle:Help:index.js.twig', array('modelfields'=>$repository->getModelFields(),'gridcolumns'=>$repository->getGridColumns()));
    }
    
    /**
     * Show create form.
     *
     * @Route("/edit", name="help_new")
     * @Method("GET")
     * @Security("has_role('ROLE_HELP_CREATE')")
     */
    public function newAction() {
        /** @var HelpRepository $repository */
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamHelpBundle:Help');
        $entity=new Help();
        return $this->render('XxamHelpBundle:Help:edit.js.twig', array('entity'=>$entity,'modelfields'=>$repository->getModelFields()));
    }

    /**
     * Show create form.
     *
     * @Route("/edit/{id}", name="help_edit")
     * @Method("GET")
     * @Security("has_role('ROLE_HELP_EDIT')")
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function editAction($id,Request $request) {
        $em = $this->getDoctrine()->getManager();
        $version=$request->get('version',null);
        /** @var HelpRepository $repository */
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamHelpBundle:Help');
        $data=[];
        if ($version){
            $entityname='Xxam\HelpBundle\Entity\Help';
            /** @var LogEntryRepository $logrepo */
            $logrepo=$em->getRepository('XxamCoreBundle:LogEntry');
            $entity= $em->find($entityname,$id);
            $logs = $logrepo->getLogEntries($entity);
            $logrepo->revert($entity, $version);
            foreach($logs as $log) {
                if ($log->getVersion()==$version){
                    $data['log']=$log;
                    break;
                }
            }

        }else{
            $entity = $repository->find($id);
            $data['log']=null;
        }


        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Help entity.');
        }
        $data['entity']=$entity;
        $data['modelfields']=$repository->getModelFields();
        return $this->render('XxamHelpBundle:Help:edit.js.twig', $data);
    }
    

    
}
