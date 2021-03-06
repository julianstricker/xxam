<?php

namespace Xxam\DynmodBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Xxam\CoreBundle\Entity\LogEntryRepository;
use Xxam\DynmodBundle\Entity\Dynmod;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Xxam\DynmodBundle\Entity\DynmodRepository;

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
     * @Security("has_role('ROLE_DYNMOD_LIST')")
     */
    public function indexAction() {
        /** @var DynmodRepository $repository */
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamDynmodBundle:Dynmod');
        return $this->render('XxamDynmodBundle:Dynmod:index.js.twig', array('modelfields'=>$repository->getModelFields(),'gridcolumns'=>$repository->getGridColumns()));
    }
    
    /**
     * Show create form.
     *
     * @Route("/edit", name="dynmod_new")
     * @Method("GET")
     * @Security("has_role('ROLE_DYNMOD_CREATE')")
     */
    public function newAction() {
        /** @var DynmodRepository $repository */
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamDynmodBundle:Dynmod');
        $entity=new Dynmod();
        return $this->render('XxamDynmodBundle:Dynmod:edit.js.twig', array('entity'=>$entity,'modelfields'=>$repository->getModelFields(),'log'=>null));
    }

    /**
     * Show create form.
     *
     * @Route("/edit/{id}", name="dynmod_edit")
     * @Method("GET")
     * @Security("has_role('ROLE_DYNMOD_EDIT')")
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function editAction($id,Request $request) {
        $em = $this->getDoctrine()->getManager();
        $version=$request->get('version',null);
        /** @var DynmodRepository $repository */
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamDynmodBundle:Dynmod');
        $data=[];
        if ($version){
            $entityname='Xxam\DynmodBundle\Entity\Dynmod';
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
            throw $this->createNotFoundException('Unable to find Dynmod entity.');
        }
        $data['entity']=$entity;
        $data['modelfields']=$repository->getModelFields();
        return $this->render('XxamDynmodBundle:Dynmod:edit.js.twig', $data);
    }

    /**
     * Lists all entities of a dynmod.
     *
     * @Route("/index/{code}", name="dynmod_dynmodindex")
     * @Method("GET")
     * @param $code
     * @return Response
     */
    public function dynmodindexAction($code) {
        /** @var DynmodRepository $repository */
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamDynmodBundle:Dynmod');
        /** @var Dynmod $dynmod */
        $dynmod=$repository->findOneBy(['code'=>$code]);
        $datacontainer=$dynmod->getDefaultDatacontainer();
       // $datacontainer->getFielddefinitions();

        return $this->render('XxamDynmodBundle:Dynmod:dynmodindex.js.twig', array('dynmod'=>$dynmod,'modelfields'=>$datacontainer ? $datacontainer->getModelFields() : null,'gridcolumns'=>$datacontainer ? $datacontainer->getGridColumns() : null));
    }

    /**
     * Lists all entities of a dynmod.
     *
     * @Route("/loaddatacontainers/{id}", name="dynmod_loaddatacontainers")
     * @Method("GET")
     * @param $id
     * @return Response
     */
    public function loaddatacontainersAction($id) {
        /** @var DynmodRepository $repository */
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamDynmodBundle:Dynmod');
        /** @var Dynmod $dynmod */
        $dynmod=$repository->findOneBy(['id'=>$id]);
        $datacontainers=[];
        foreach($dynmod->getDatacontainers() as $datacontainer){
            $datacontainers[]=$datacontainer->toGridArray();
        }
        return new Response(json_encode(['datacontainers'=>$datacontainers]));
    }
}
