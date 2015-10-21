<?php

namespace Xxam\UserBundle\Controller;

use Xxam\UserBundle\Entity\Group;
use Xxam\UserBundle\Form\Type\GroupType;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\View as FOSView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Voryx\RESTGeneratorBundle\Controller\VoryxController;

/**
 * Group controller.
 * @RouteResource("Group")
 */
class GroupRESTController extends VoryxController
{
    use Base\UserTrait;
    /**
     * Get a Group entity
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @return Response
     * 
     * @Security("has_role('ROLE_GROUP_LIST')")
     *
     */
    public function getAction(Group $entity)
    {
        return $entity;
    }
    /**
     * Get all Group entities.
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param ParamFetcherInterface $paramFetcher
     * 
     * @Security("has_role('ROLE_GROUP_LIST')")
     *
     * @return Response
     *
     * @QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing notes.")
     * @QueryParam(name="limit", requirements="\d+", default="20", description="How many notes to return.")
     * @QueryParam(name="order_by", nullable=true, array=true, description="Order by fields. Must be an array ie. &order_by[name]=ASC&order_by[description]=DESC")
     * @QueryParam(name="filters", nullable=true, array=true, description="Filter by fields. Must be an array ie. &filters[id]=3")
     */
    public function cgetAction(ParamFetcherInterface $paramFetcher)
    {
        try {
            $offset = $paramFetcher->get('offset');
            $limit = $paramFetcher->get('limit');
            $order_by = $paramFetcher->get('order_by');
            $filters = !is_null($paramFetcher->get('filters')) ? $paramFetcher->get('filters') : array();

            $em = $this->getDoctrine()->getManager();
            $entities = $em->getRepository('XxamUserBundle:Group')->findBy($filters, $order_by, $limit, $offset);
            if ($entities) {
                //total:
                $total = $em->getRepository('XxamUserBundle:Group')->getTotalcount($filters);
                $results=Array();
                foreach($entities as $entity){
                    $results[]=$entity->toGridObject();
                }
                return array('groups'=>$results, 'limit'=>$limit,'offset'=>$offset, 'totalCount'=>$total);
            }

            return FOSView::create('Not Found', Codes::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Create a Group entity.
     *
     * @View(statusCode=201, serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * 
     * @Security("has_role('ROLE_GROUP_CREATE')")
     *
     * @return Response
     *
     */
    public function postAction(Request $request)
    {
        $entity = new Group('',$this->getRoles());
        $form = $this->createForm(new GroupType($this->getRoles()), $entity, array("method" => $request->getMethod()));
        $this->removeExtraFields($request, $form);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $entity->toGridObject();
        }

        return FOSView::create(array('errors' => $form->getErrors()), Codes::HTTP_INTERNAL_SERVER_ERROR);
    }
    /**
     * Update a Group entity.
     *
     * @View(serializerEnableMaxDepthChecks=false)
     *
     * @param Request $request
     * @param $entity
     * 
     * @Security("has_role('ROLE_GROUP_EDIT')")
     *
     * @return Response
     */
    public function putAction(Request $request, Group $entity)
    {
        
        try {
            $em = $this->getDoctrine()->getManager();
            
            //$request->setMethod('PATCH'); //Treat all PUTs as PATCH
            $form = $this->createForm(new GroupType($this->getRoles()), $entity, array("method" => $request->getMethod()));
            $this->removeExtraFields($request, $form);
            $form->handleRequest($request);
            
            if ($form->isValid()) {
                $em->flush();

                return $entity->toGridObject();
            }
            //dump($form->getData());
            return FOSView::create(array('errors' => $form->getErrors()), Codes::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            dump($e->getMessage());
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Partial Update to a Group entity.
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param $entity
     * 
     * @Security("has_role('ROLE_GROUP_EDIT')")
     *
     * @return Response
*/
    public function patchAction(Request $request, Group $entity)
    {
        return $this->putAction($request, $entity);
    }
    /**
     * Delete a Group entity.
     *
     * @View(statusCode=204)
     *
     * @param Request $request
     * @param $entity
     * @internal param $id
     * 
     * @Security("has_role('ROLE_GROUP_DELETE')")
     *
     * @return Response
     */
    public function deleteAction(Request $request, Group $entity)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($entity);
            $em->flush();

            return null;
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    
}
