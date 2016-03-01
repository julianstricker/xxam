<?php

namespace Xxam\DynmodBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Xxam\CoreBundle\Entity\LogEntryRepository;
use Xxam\DynmodBundle\Entity\Dynmod;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Dynmod controller.
 *
 * @Route("/dynmod")
 */
class DynmodController extends Controller
{


    
    
    /**
     * Lists all Dynmod entities.
     *
     * @Route("", name="dynmod")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_DYNMOD_LIST')")
     */
    public function indexAction() {
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamDynmodBundle:Dynmod');
        return $this->render('XxamDynmodBundle:Dynmod:index.js.twig', array('modelfields'=>$repository->getModelFields(),'gridcolumns'=>$repository->getGridColumns()));
    }
    
    /**
     * Show create form.
     *
     * @Route("/edit", name="dynmod_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_DYNMOD_CREATE')")
     */
    public function newAction() {
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamDynmodBundle:Dynmod');
        $entity=new Dynmod();
        return $this->render('XxamDynmodBundle:Dynmod:edit.js.twig', array('entity'=>$entity,'modelfields'=>$repository->getModelFields()));
    }
    
    /**
     * Show create form.
     *
     * @Route("/edit/{id}", name="dynmod_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_DYNMOD_EDIT')")
     */
    public function editAction($id,Request $request) {
        $em = $this->getDoctrine()->getManager();
        $version=$request->get('version',null);
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamDynmodBundle:Dynmod');
        if ($version){
            $entityname='Xxam\DynmodBundle\Entity\Dynmod';
            /** @var LogEntryRepository $logrepo */
            $logrepo=$em->getRepository('XxamCoreBundle:LogEntry');
            $entity= $em->find($entityname,$id);
            //$logs = $logrepo->getLogEntries($entity);
            $logrepo->revert($entity, $version);
        }else{
            $entity = $repository->find($id);
        }


        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Dynmod entity.');
        }

        return $this->render('XxamDynmodBundle:Dynmod:edit.js.twig', array('entity'=>$entity,'modelfields'=>$repository->getModelFields()));
    }
    

    
}
