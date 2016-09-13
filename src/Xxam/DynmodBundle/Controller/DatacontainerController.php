<?php

namespace Xxam\DynmodBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Xxam\CoreBundle\Entity\LogEntryRepository;
use Xxam\DynmodBundle\Entity\DatacontainerRepository;
use Xxam\DynmodBundle\Entity\Datacontainer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Xxam\DynmodBundle\Entity\DynmodRepository;

/**
 * Dynmod Datacontainer controller.
 *
 * @Route("/dynmod/datacontainer")
 */
class DatacontainerController extends Controller
{


    
    
    /**
     * Lists all Datacontainer entities.
     *
     * @Route("", name="datacontainer")
     * @Method("GET")
     * @Security("has_role('ROLE_DYNMOD_DATACONTAINER_LIST')")
     */
    public function indexAction() {
        /** @var DatacontainerRepository $repository */
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamDynmodBundle:Datacontainer');
        return $this->render('XxamDynmodBundle:Datacontainer:index.js.twig', array('modelfields'=>$repository->getModelFields(),'gridcolumns'=>$repository->getGridColumns()));
    }
    
    /**
     * Show create form.
     *
     * @Route("/edit", name="datacontainer_new")
     * @Method("GET")
     * @Security("has_role('ROLE_DYNMOD_DATACONTAINER_CREATE')")
     */
    public function newAction() {
        /** @var DatacontainerRepository $repository */
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamDynmodBundle:Datacontainer');
        $entity=new Datacontainer();
        $dynmods=$this->getDoctrine()->getManager()->getRepository('XxamDynmodBundle:Dynmod')->findAll();
        return $this->render('XxamDynmodBundle:Datacontainer:edit.js.twig', array('entity'=>$entity,'dynmods'=>$dynmods,'modelfields'=>$repository->getModelFields(),'log'=>null));
    }

    /**
     * Show create form.
     *
     * @Route("/edit/{id}", name="datacontainer_edit")
     * @Method("GET")
     * @Security("has_role('ROLE_DYNMOD_DATACONTAINER_EDIT')")
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function editAction($id,Request $request) {
        $em = $this->getDoctrine()->getManager();
        $version=$request->get('version',null);
        /** @var DatacontainerRepository $repository */
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamDynmodBundle:Datacontainer');
        $data=[];
        if ($version){
            $entityname='Xxam\DynmodBundle\Entity\Datacontainer';
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
            throw $this->createNotFoundException('Unable to find Datacontainer entity.');
        }
        $data['entity']=$entity;
        $data['modelfields']=$repository->getModelFields();
        $dynmods=$this->getDoctrine()->getManager()->getRepository('XxamDynmodBundle:Dynmod')->findAll();
        $data['dynmods']=$dynmods;
        return $this->render('XxamDynmodBundle:Datacontainer:edit.js.twig', $data);
    }


}
