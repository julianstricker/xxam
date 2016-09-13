<?php

namespace Xxam\MailclientBundle\Controller;

use Xxam\MailclientBundle\Entity\Mailspool;
use Xxam\MailclientBundle\Entity\MailspoolRepository;

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
 * Mailspool controller.
 * @RouteResource("Mailspool")
 */
class MailspoolRESTController extends BaseRestController
{
    /**
     * Get a Mailspool entity
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @return Response
     * @Security("has_role('ROLE_CONTACT_LIST')")
     *
     */
    public function getAction(Mailspool $entity)
    {
        return $entity;
    }

    /**
     * Get all Mailspool entities.
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     * @return array|FOSView
     * @QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing notes.")
     * @QueryParam(name="limit", requirements="\d+", default="20", description="How many notes to return.")
     * @QueryParam(name="order_by", nullable=true, array=true, description="Order by fields. Must be an array ie. &order_by[name]=ASC&order_by[description]=DESC")
     * @QueryParam(name="filters", nullable=true, array=true, description="Filter by fields. Must be an array ie. &filters[id]=3")
     *
     * @Security("has_role('ROLE_CONTACT_LIST')")
     */
    public function cgetAction(Request $request, ParamFetcherInterface $paramFetcher)
    {
        try {
            $offset = $paramFetcher->get('offset');
            $limit = $paramFetcher->get('limit');
            $order_by = $paramFetcher->get('order_by');
            $filters = !is_null($paramFetcher->get('filters')) ? $paramFetcher->get('filters') : array();

            $em = $this->getDoctrine()->getManager();
            $entities = $em->getRepository('XxamMailclientBundle:Mailspool')->findBy($filters, $order_by, $limit, $offset);
            if ($entities) {
                //total:
                /** @var MailspoolRepository $repository */
                $repository=$em->getRepository('XxamMailclientBundle:Mailspool');
                $total = $repository->getTotalcount($filters);
                $results=Array();
                /** @var Filesystem $entity */
                foreach($entities as $entity){
                    $results[]=$entity->toGridObject($request->getSession()->get('timezone'));
                }

                return array('mailspools'=>$results, 'limit'=>$limit,'offset'=>$offset, 'totalCount'=>$total);
            }

            return FOSView::create('Not Found', Codes::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete a Mailspool entity.
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
    public function deleteAction(Mailspool $entity)
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
