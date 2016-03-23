<?php

namespace Xxam\ContactBundle\Controller;

use Xxam\ContactBundle\Entity\Contact;
use Xxam\ContactBundle\Entity\ContactRepository;
use Xxam\ContactBundle\Form\Type\ContactType;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\View as FOSView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Xxam\CoreBundle\Controller\Base\BaseRestController;

/**
 * Contact controller.
 * @RouteResource("Contact")
 */
class ContactRESTController extends BaseRestController
{
    /**
     * Get a Contact entity
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @return Response
     * @Security("has_role('ROLE_CONTACT_LIST')")
     *
     */
    public function getAction(Contact $entity)
    {
        return $entity;
    }
    /**
     * Get all Contact entities.
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return Response
     *
     * @QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing notes.")
     * @QueryParam(name="limit", requirements="\d+", default="20", description="How many notes to return.")
     * @QueryParam(name="order_by", nullable=true, array=true, description="Order by fields. Must be an array ie. &order_by[name]=ASC&order_by[description]=DESC")
     * @QueryParam(name="filters", nullable=true, array=true, description="Filter by fields. Must be an array ie. &filters[id]=3")
     * 
     * @Security("has_role('ROLE_CONTACT_LIST')")
     */
    public function cgetAction(ParamFetcherInterface $paramFetcher)
    {
        try {
            $offset = $paramFetcher->get('offset');
            $limit = $paramFetcher->get('limit');
            $order_by = $paramFetcher->get('order_by');
            $filters = !is_null($paramFetcher->get('filters')) ? $paramFetcher->get('filters') : array();

            $em = $this->getDoctrine()->getManager();
            $entities = $em->getRepository('XxamContactBundle:Contact')->findBy($filters, $order_by, $limit, $offset);
            if ($entities) {
                //total:
                /** @var ContactRepository $repository */
                $repository=$em->getRepository('XxamContactBundle:Contact');
                $total = $repository->getTotalcount($filters);
                return array('contacts'=>$entities, 'limit'=>$limit,'offset'=>$offset, 'totalCount'=>$total);
            }

            return FOSView::create('Not Found', Codes::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Create a Contact entity.
     *
     * @View(statusCode=201, serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     *
     * @return Response
     * @Security("has_role('ROLE_CONTACT_CREATE')")
     *
     */
    public function postAction(Request $request)
    {
        $entity = new Contact();
        //new ContactType($this->container->getParameter('contacttypes')
        $form = $this->createForm('Xxam\ContactBundle\Form\Type\ContactType', $entity, array("method" => $request->getMethod(),'contacttypes' => $this->container->getParameter('contacttypes')));
        $this->removeExtraFields($request, $form);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $entity;
        }

        return FOSView::create(array('errors' => $form->getErrors()), Codes::HTTP_INTERNAL_SERVER_ERROR);
    }
    /**
     * Update a Contact entity.
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param $entity
     *
     * @return Response
     * @Security("has_role('ROLE_CONTACT_EDIT')")
     */
    public function putAction(Request $request, Contact $entity)
    {
        
        try {
            $em = $this->getDoctrine()->getManager();
            
            //$request->setMethod('PATCH'); //Treat all PUTs as PATCH
            $form = $this->createForm('Xxam\ContactBundle\Form\Type\ContactType', $entity, array("method" => $request->getMethod(),'contacttypes' => $this->container->getParameter('contacttypes')));

            $this->removeExtraFields($request, $form);
            $form->handleRequest($request);
            
            if ($form->isValid()) {
                $em->flush();

                return $entity;
            }
            //dump($form->getData());
            return FOSView::create(array('errors' => $form->getErrors()), Codes::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            dump($e->getMessage());
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Partial Update to a Contact entity.
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param $entity
     *
     * @return Response
     * 
     * @Security("has_role('ROLE_CONTACT_EDIT')")
*/
    public function patchAction(Request $request, Contact $entity)
    {
        return $this->putAction($request, $entity);
    }
    /**
     * Delete a Contact entity.
     *
     * @View(statusCode=204)
     *
     * @param $entity
     * @internal param $id
     *
     * @return Response
     * 
     * @Security("has_role('ROLE_CONTACT_DELETE')")
     */
    public function deleteAction(Contact $entity)
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
