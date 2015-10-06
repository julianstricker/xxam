<?php

namespace Just\ContactBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Just\ContactBundle\Entity\Contact;
use Just\ContactBundle\Form\Type\ContactType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Contact controller.
 *
 * @Route("/contact")
 */
class ContactController extends Controller
{

    /**
     * Search contacts for autocomplete box.
     *
     * @Route("/searchcontacts", name="contact_searchcontacts")
     * @Method("GET")
     * @Security("has_role('ROLE_CONTACT_LIST')")
     */
    public function searchcontactsAction(Request $request)
    {
        
        $queryparam = $request->get('query');

        $returnvalues=Array();
        $em = $this->getDoctrine()->getManager();
        $query = $em->getRepository('JustContactBundle:Contact')->createQueryBuilder('e');
        $query->andWhere('(e.lastname LIKE :queryparam1 OR e.firstname LIKE :queryparam2)');
        $query->setParameter('queryparam1', '%'.$queryparam.'%');
        $query->setParameter('queryparam2', '%'.$queryparam.'%');
        $entities = $query->getQuery()->getResult();
        if ($entities) {
            
            foreach($entities as $entity){
                $returnvalues[]=Array('value'=>$entity->getLastname().' '.$entity->getFirstname());
            }
            
        }

        $response = new Response(json_encode(Array('contacts'=>$returnvalues)));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response; 
        
    }
    
    
    /**
     * Lists all Contact entities.
     *
     * @Route("/", name="contact")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_CONTACT_LIST')")
     */
    public function indexAction() {
        $repository=$this->getDoctrine()->getManager()->getRepository('JustContactBundle:Contact');
        return $this->render('JustContactBundle:Contact:index.js.twig', array('modelfields'=>$repository->getModelFields(),'gridcolumns'=>$repository->getGridColumns()));
    }
    
    /**
     * Show create form.
     *
     * @Route("/edit", name="contact_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_CONTACT_CREATE')")
     */
    public function newAction() {
        $repository=$this->getDoctrine()->getManager()->getRepository('JustContactBundle:Contact');
        $entity=new Contact();
        return $this->render('JustContactBundle:Contact:edit.js.twig', array('entity'=>$entity,'contacttypes'=>$this->contacttypesAsKeyValue(),'modelfields'=>$repository->getModelFields()));
    }
    
    /**
     * Show create form.
     *
     * @Route("/edit/{id}", name="contact_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_CONTACT_EDIT')")
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();
        $repository=$this->getDoctrine()->getManager()->getRepository('JustContactBundle:Contact');

        $entity = $repository->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Contact entity.');
        }
        $contacttypes=Array();
        foreach($this->container->getParameter('contacttypes') as $key => $value){
            $contacttypes[]=Array('id'=>$key,'value'=>$value);
        }
        return $this->render('JustContactBundle:Contact:edit.js.twig', array('entity'=>$entity,'contacttypes'=>$this->contacttypesAsKeyValue(),'modelfields'=>$repository->getModelFields()));
    }
    
    private function contacttypesAsKeyValue(){
        $contacttypes=Array();
        foreach($this->container->getParameter('contacttypes') as $key => $value){
            $contacttypes[]=Array('id'=>$key,'value'=>$value);
        }
        return $contacttypes;
    }
    
}
