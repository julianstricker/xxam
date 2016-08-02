<?php

namespace Xxam\ContactBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Xxam\ContactBundle\Entity\Contact;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use LinkedIn\LinkedIn;

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
        $query = $em->getRepository('XxamContactBundle:Contact')->createQueryBuilder('e');
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
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamContactBundle:Contact');
        return $this->render('XxamContactBundle:Contact:index.js.twig', array('modelfields'=>$repository->getModelFields(),'gridcolumns'=>$repository->getGridColumns()));
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
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamContactBundle:Contact');
        $entity=new Contact();
        return $this->render('XxamContactBundle:Contact:edit.js.twig', array('entity'=>$entity,'contacttypes'=>$this->contacttypesAsKeyValue(),'modelfields'=>$repository->getModelFields()));
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
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamContactBundle:Contact');

        $entity = $repository->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Contact entity.');
        }
        $contacttypes=Array();
        foreach($this->container->getParameter('contacttypes') as $key => $value){
            $contacttypes[]=Array('id'=>$key,'value'=>$value);
        }
        return $this->render('XxamContactBundle:Contact:edit.js.twig', array('entity'=>$entity,'contacttypes'=>$this->contacttypesAsKeyValue(),'modelfields'=>$repository->getModelFields()));
    }

    /**
     * Get Linkedin Contact infos
     *
     * @Route("/getlinkedincontact/{email}", name="contact_getlinkedincontact")
     * @Method("GET")
     * @Security("has_role('ROLE_CONTACT_EDIT')")
     */
    /*public function getlinkedincontactAction($email) {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $li = new LinkedIn(
            array(
                'api_key' => '787fjpije7jmt7',
                'api_secret' => 'sb4ndNUGhGaZYFaG',
                'callback_url' => 'https://xxam.com/contact/linkedinoauth'
            )
        );
        $url = $li->getLoginUrl(
            array(
                LinkedIn::SCOPE_BASIC_PROFILE,
                LinkedIn::SCOPE_EMAIL_ADDRESS,
                //LinkedIn::SCOPE_NETWORK
            )
        );
        $memcached=$this->get('memcached');
        $token= $memcached->get('linkedin_token_'.$user->getTenantId());
        if (!$token){
            $url = $li->getLoginUrl(
                array(
                    LinkedIn::SCOPE_BASIC_PROFILE,
                    LinkedIn::SCOPE_EMAIL_ADDRESS,
                    //LinkedIn::SCOPE_NETWORK
                )
            );
            $info = $li->get('/people/~:(first-name,last-name,positions)');
            $response = new Response(json_encode(Array('status'=>'auth','url'=>$url)));
            return $response;
        }else{
            $li->setAccessToken($token);
            $info = $li->get('/people/~:(first-name,last-name,positions)');
            $response = new Response(json_encode(Array('status'=>'OK','data'=>$info)));
            return $response;
        }


    }*/

    /**
     * Get Linkedin Contact infos
     *
     * @Route("/linkedinoauth", name="contact_linkedinoauth")
     * @Method("GET")
     */
    /*public function linkedinoauthAction() {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $li = new LinkedIn(
            array(
                'api_key' => '787fjpije7jmt7',
                'api_secret' => 'sb4ndNUGhGaZYFaG',
                'callback_url' => 'https://xxam.com/contact/linkedinoauth'
            )
        );
        $token = $li->getAccessToken($_REQUEST['code']);
        $token_expires = $li->getAccessTokenExpiration();
        $memcached=$this->get('memcached');
        $memcached->set('linkedin_token_'.$user->getTenantId(),$token);
        $response = new Response(json_encode(Array('status'=>'OK','token'=>$token_expires)));
        return $response;
    }*/


    
    private function contacttypesAsKeyValue(){
        $contacttypes=Array();
        foreach($this->container->getParameter('contacttypes') as $key => $value){
            $contacttypes[]=Array('id'=>$key,'value'=>$value);
        }
        return $contacttypes;
    }
    
}
