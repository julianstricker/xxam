<?php

namespace Just\CmsAdminBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Just\CmsAdminBundle\Entity\CmsAdmin;
use Just\CmsAdminBundle\Form\CmsAdminType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * CmsAdmin controller.
 *
 * @Route("/cmsadmin")
 */
class CmsAdminController extends Controller
{


    /**
     * @Template()
     */
    public function pageAction($contentDocument)
    {
        $dm = $this->get('doctrine_phpcr')->getManager();
        $posts = $dm->getRepository('JustCmsAdminBundle:Post')->findAll();

        return array(
            'page'  => $contentDocument,
            'posts' => $posts,
        );
    }

    
    /**
     * Lists all CmsAdmin entities.
     *
     * @Route("/", name="cmsadmin")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_CMSADMIN_LIST')")
     */
    public function indexAction() {
        $repository=$this->getDoctrine()->getManager()->getRepository('JustCmsAdminBundle:CmsAdmin');
        return $this->render('JustCmsAdminBundle:CmsAdmin:index.js.twig', array('modelfields'=>$repository->getModelFields(),'gridcolumns'=>$repository->getGridColumns()));
    }
    
    /**
     * Show create form.
     *
     * @Route("/edit", name="cmsadmin_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_CMSADMIN_CREATE')")
     */
    public function newAction() {
        $repository=$this->getDoctrine()->getManager()->getRepository('JustCmsAdminBundle:CmsAdmin');
        $entity=new CmsAdmin();
        return $this->render('JustCmsAdminBundle:CmsAdmin:edit.js.twig', array('entity'=>$entity,'cmsadmintypes'=>$this->cmsadmintypesAsKeyValue(),'modelfields'=>$repository->getModelFields()));
    }
    
    /**
     * Show create form.
     *
     * @Route("/edit/{id}", name="cmsadmin_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_CMSADMIN_EDIT')")
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();
        $repository=$this->getDoctrine()->getManager()->getRepository('JustCmsAdminBundle:CmsAdmin');

        $entity = $repository->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find CmsAdmin entity.');
        }
        $cmsadmintypes=Array();
        foreach($this->container->getParameter('cmsadmintypes') as $key => $value){
            $cmsadmintypes[]=Array('id'=>$key,'value'=>$value);
        }
        return $this->render('JustCmsAdminBundle:CmsAdmin:edit.js.twig', array('entity'=>$entity,'cmsadmintypes'=>$this->cmsadmintypesAsKeyValue(),'modelfields'=>$repository->getModelFields()));
    }

    
}
