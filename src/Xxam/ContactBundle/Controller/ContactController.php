<?php

namespace Xxam\ContactBundle\Controller;


use Hybrid_Auth;
use Hybrid_Endpoint;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Xxam\ContactBundle\Entity\Address;
use Xxam\ContactBundle\Entity\Communicationdata;
use Xxam\ContactBundle\Entity\Contact;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Xxam\ContactBundle\Entity\ContactRepository;
use Xxam\ContactBundle\Entity\Image;
use Hybrid\Auth;

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
     * Get images for email.
     *
     * @Route("/getemailimages/{email}", name="contact_getemailimages")
     * @Method("GET")
     * @Security("has_role('ROLE_CONTACT_LIST')")
     * @param $email
     * @return Response
     */
    public function getemailimagesAction($email) {
        $email=strtolower(trim($email));
        $images=[];
        try{
            $image=file_get_contents('http://www.gravatar.com/avatar/'.md5($email).'.jpg?s=200&d=404');
        }catch(\Exception $e){

        }
        if(isset($image))$images[]='http://www.gravatar.com/avatar/'.md5($email).'.jpg?s=200&d=404';

        $config = $this->getXingConfig();

        require_once $this->get('kernel')->getRootDir().'/../vendor/hybridauth/hybridauth/hybridauth/Hybrid/Auth.php';
        try {
            $oHybridAuth = new \Hybrid_Auth($config);
            $oXING       = $oHybridAuth->authenticate('XING');
            $xingdata=$oXING->api()->get('users/find_by_emails', array(
                'user_fields' => 'id,photo_urls',
                'emails' => $email
            ));

        }
        catch(\Exception $e) {

        }

        if (
            isset($xingdata)
            && property_exists($xingdata, 'results')
            && property_exists($xingdata->results, 'items')
            && count($xingdata->results->items)>0)
        {
            foreach($xingdata->results->items as $item){
                if(property_exists($item, 'user')
                    && !is_null($item->user)
                    && property_exists($item->user, 'photo_urls')
                    && property_exists($item->user->photo_urls, 'size_192x192')
                ){
                    $images[]=$item->user->photo_urls->size_192x192;
                }
            }
        }



        return new Response(json_encode($images));
    }


    /**
     * Get images for email.
     *
     * @Route("/getcontactdataforemail/{email}", name="contact_getcontactdataforemail")
     * @Method("GET")
     * @Security("has_role('ROLE_CONTACT_LIST')")
     * @param $email
     * @return Response
     */
    public function getcontactdataforemailAction($email) {
        $email=strtolower(trim($email));

        /** @var ContactRepository $repository */
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamContactBundle:Contact');

        $entity = $repository->findOneByEmail($email);
        if (!$entity){
            $entity=new Contact();
        }
        $this->mapXingDataToContact($email,$entity);


        try{
            $image=file_get_contents('http://www.gravatar.com/avatar/'.md5($email).'.jpg?s=200&d=404');
        }catch(\Exception $e){

        }
        if(isset($image)){
            $images[]='http://www.gravatar.com/avatar/'.md5($email).'.jpg?s=200&d=404';
            $image=new Image();
            $image->setOrigin('http://www.gravatar.com/avatar/'.md5($email).'.jpg?s=200&d=404');
            $image->setUri('http://www.gravatar.com/avatar/'.md5($email).'.jpg?s=200&d=404');
            $image->setUpdated(new \DateTime());
            $entity->addImage($image);
        }
        $serializer=$this->get('serializer');





        return new Response($serializer->serialize($entity,'json'));
    }




    private function mapXingDataToContact($email,Contact $contact){
        $config = $this->getXingConfig();

        require_once $this->get('kernel')->getRootDir().'/../vendor/hybridauth/hybridauth/hybridauth/Hybrid/Auth.php';
        try {
            $oHybridAuth = new \Hybrid_Auth($config);
            $oXING       = $oHybridAuth->authenticate('XING');
            $xingdata=$oXING->api()->get('users/find_by_emails', array(
                'user_fields' => 'id,first_name,last_name,display_name,permalink,gender,birth_date,active_email,time_zone,interests,organisation_member,languages,private_address,business_address,web_profiles,instant_messaging_accounts,professional_experience,photo_urls',
                'emails' => $email
            ));

        }
        catch(\Exception $e) {

        }
        //dump($xingdata);
        $xc=false;
        if (
            isset($xingdata)
            && property_exists($xingdata, 'results')
            && property_exists($xingdata->results, 'items')
            && count($xingdata->results->items)>0)
        {
            $xc=$xingdata->results->items[0];
        }

        if($xc && $xc->user){
            $xuser=$xc->user;
            $contact->setFirstname($xuser->first_name);
            $contact->setLastname($xuser->last_name);
            $contact->setGender($xuser->gender);
            $contact->setBirthday(new \DateTime($xuser->birth_date->year.'-'.$xuser->birth_date->month.'-'.$xuser->birth_date->day));
            $contact->setTimezone($xuser->time_zone->name);
            $contact->setNotes($xuser->interests);
            //Addresses:

            //Private:
            if ($xuser->private_address->city!=null) {
                $address = new Address();
                $address->setTimezone($xuser->time_zone->name);
                $address->setAddresstypeId(5);
                $address->setAddress($xuser->private_address->street);
                $address->setLocality($xuser->private_address->city);
                $address->setZip($xuser->private_address->zip_code);
                $address->setRegion($xuser->private_address->province);
                $address->setCountrycode($xuser->private_address->country);
                $contact->addAddress($address);
            }
            //Work:
            if ($xuser->business_address->city!=null) {
                $address = new Address();
                $address->setTimezone($xuser->time_zone->name);
                $address->setAddresstypeId(6);
                $address->setAddress($xuser->business_address->street);
                $address->setLocality($xuser->business_address->city);
                $address->setZip($xuser->business_address->zip_code);
                $address->setRegion($xuser->business_address->province);
                $address->setCountrycode($xuser->business_address->country);
                $contact->addAddress($address);
            }
            //Private Email:
            if ($xuser->private_address->email!=null) {
                $communicationdata=new Communicationdata();
                $communicationdata->setCommunicationdatatypeId('email_private');
                $communicationdata->setValue($xuser->private_address->email);
                $contact->addCommunicationdata($communicationdata);
            }
            //Work Email:
            if ($xuser->business_address->email!=null) {
                $communicationdata=new Communicationdata();
                $communicationdata->setCommunicationdatatypeId('email_business');
                $communicationdata->setValue($xuser->business_address->email);
                $contact->addCommunicationdata($communicationdata);
            }
            //Private Phone:
            if ($xuser->private_address->phone!=null) {
                $communicationdata=new Communicationdata();
                $communicationdata->setCommunicationdatatypeId('phone_private');
                $communicationdata->setValue($xuser->private_address->phone);
                $contact->addCommunicationdata($communicationdata);
            }
            //Work Phone:
            if ($xuser->business_address->phone!=null) {
                $communicationdata=new Communicationdata();
                $communicationdata->setCommunicationdatatypeId('phone_business');
                $communicationdata->setValue($xuser->business_address->phone);
                $contact->addCommunicationdata($communicationdata);
            }
            //Private Mobile Phone:
            if ($xuser->private_address->mobile_phone!=null) {
                $communicationdata=new Communicationdata();
                $communicationdata->setCommunicationdatatypeId('phone_mobileprivate');
                $communicationdata->setValue($xuser->private_address->mobile_phone);
                $contact->addCommunicationdata($communicationdata);
            }
            //Work Mobile Phone:
            if ($xuser->business_address->mobile_phone!=null) {
                $communicationdata=new Communicationdata();
                $communicationdata->setCommunicationdatatypeId('phone_mobilebusiness');
                $communicationdata->setValue($xuser->business_address->mobile_phone);
                $contact->addCommunicationdata($communicationdata);
            }

            //webprofiles:
            if ($xuser->web_profiles!=null) {
                if (property_exists($xuser->web_profiles, 'homepage') && is_array($xuser->web_profiles->homepage)) {
                    foreach($xuser->web_profiles->homepage as $url){
                        $communicationdata=new Communicationdata();
                        $communicationdata->setCommunicationdatatypeId('web_homepage');
                        $communicationdata->setValue($url);
                        $contact->addCommunicationdata($communicationdata);
                    }
                }
                if (property_exists($xuser->web_profiles, 'blog') && is_array($xuser->web_profiles->blog)) {
                    foreach($xuser->web_profiles->blog as $url){
                        $communicationdata=new Communicationdata();
                        $communicationdata->setCommunicationdatatypeId('web_blog');
                        $communicationdata->setValue($url);
                        $contact->addCommunicationdata($communicationdata);
                    }
                }
                if (property_exists($xuser->web_profiles, 'twitter') && is_array($xuser->web_profiles->twitter)) {
                    foreach($xuser->web_profiles->twitter as $url){
                        $communicationdata=new Communicationdata();
                        $communicationdata->setCommunicationdatatypeId('web_twitter');
                        $communicationdata->setValue($url);
                        $contact->addCommunicationdata($communicationdata);
                    }
                }
            }

            if (property_exists($xuser->instant_messaging_accounts, 'icq') && $xuser->instant_messaging_accounts->icq!=null) {
                $communicationdata=new Communicationdata();
                $communicationdata->setCommunicationdatatypeId('im_icq');
                $communicationdata->setValue($xuser->instant_messaging_accounts->icq);
                $contact->addCommunicationdata($communicationdata);
            }
            if (property_exists($xuser->instant_messaging_accounts, 'icq') && $xuser->instant_messaging_accounts->msn!=null) {
                $communicationdata=new Communicationdata();
                $communicationdata->setCommunicationdatatypeId('im_msn');
                $communicationdata->setValue($xuser->instant_messaging_accounts->msn);
                $contact->addCommunicationdata($communicationdata);
            }

            if ($xuser->professional_experience->primary_company!=null) {

                $contact->setOrganizationname($xuser->professional_experience->primary_company->name);
                $contact->setOrganizationfunction($xuser->professional_experience->primary_company->title);
            }
            if ($xuser->photo_urls->size_original!=null) {
                $image=new Image();
                $image->setOrigin($xuser->photo_urls->size_original);
                $image->setUri($xuser->photo_urls->size_original);
                $image->setUpdated(new \DateTime());
                $contact->addImage($image);

            }
        }
    }


    private function getXingConfig(){
        $config=$this->getParameter('xxam_contact.xing');

        return array(
            // "base_url" the url that point to HybridAuth Endpoint (where the index.php and config.php are found)
            "base_url" => $this->container->get('router')->generate('contact_hauth'),

            "providers" => array (
                "XING" => array (
                    "enabled" => true,
                    "wrapper" => array(
                        "path" => $this->get('kernel')->getRootDir().'/../vendor/hybridauth/hybridauth/additional-providers/hybridauth-xing/Providers/XING.php',
                        "class" => "Hybrid_Providers_XING"
                    ),
                    "keys" => array ( "key" =>$config['key'], "secret" =>$config['secret'] )

                )
            )
        );
    }

    /**
     * hybridauth url
     *
     * @Route("/hauth", name="contact_hauth")
     * @Method("GET")
     * @param Request $request
     * @return Response
     */
    public function hauthAction(Request $request) {


        require_once $this->get('kernel')->getRootDir().'/../vendor/hybridauth/hybridauth/hybridauth/Hybrid/Auth.php';
        require_once( $this->get('kernel')->getRootDir().'/../vendor/hybridauth/hybridauth/hybridauth/Hybrid/Endpoint.php');

        \Hybrid_Endpoint::process();
        return new Response('');
    }

    /**
     * Get Linkedin Contact infos
     *
     * @Route("/getlinkedincontact/{email}", name="contact_getlinkedincontact")
     * @Method("GET")
     * @Security("has_role('ROLE_CONTACT_EDIT')")
     */
    public function getlinkedincontactAction($email) {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hybridauth = new Hybrid_Auth([
            "base_url" => "https://xxam.com/contact/linkedinoauth",
            "providers" => [
                "LinkedIn" => array ( // 'id' is your facebook application id
                    "enabled" => true,
                    "keys" => array ( "key" => "787fjpije7jmt7", "secret" => "sb4ndNUGhGaZYFaG" ),
                    "scope" => "r_basicprofile, r_emailaddress"
                ),
            ]
        ]);
        $li = $hybridauth->authenticate("LinkedIn");
        dump($li->getUserProfile());

        $query    = 'https://api.linkedin.com/v1/people/~:julian@julianstricker.com';
        $info = $li->api()->profile('~:(id,first-name,last-name,public-profile-url,picture-url,email-address,date-of-birth,phone-numbers,summary)');

        dump($info);

        return new Response('');

    }

    /**
     * Get Linkedin Contact infos
     *
     * @Route("/linkedinoauth", name="contact_linkedinoauth")
     * @Method("GET")
     */
    public function linkedinoauthAction() {
        Hybrid_Endpoint::process();
    }


    
    private function contacttypesAsKeyValue(){
        $contacttypes=Array();
        foreach($this->container->getParameter('contacttypes') as $key => $value){
            $contacttypes[]=Array('id'=>$key,'value'=>$value);
        }
        return $contacttypes;
    }
    
}
