<?php

namespace Xxam\UserBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Xxam\UserBundle\Entity\User;
use Xxam\UserBundle\Entity\UserRepository;
use Xxam\UserBundle\Form\Type\UserType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * User controller.
 *
 * @Route("/user")
 */
class UserController extends Controller
{
    use Base\UserTrait;

    /**
     * Search users for autocomplete box.
     *
     * @Route("/searchusers", name="user_searchusers")
     * @Method("GET")
     * 
     * @Security("has_role('ROLE_USER_LIST')")
     * 
     */
    public function searchusersAction(Request $request)
    {
        
        $queryparam = $request->get('query');

        $returnvalues=Array();
        $em = $this->getDoctrine()->getManager();
        $query = $em->getRepository('XxamUserBundle:User')->createQueryBuilder('e');
        $query->andWhere('(e.lastname LIKE :queryparam1 OR e.firstname LIKE :queryparam2)');
        $query->setParameter('queryparam1', '%'.$queryparam.'%');
        $query->setParameter('queryparam2', '%'.$queryparam.'%');
        $entities = $query->getQuery()->getResult();
        if ($entities) {
            
            foreach($entities as $entity){
                $returnvalues[]=Array('value'=>$entity->getLastname().' '.$entity->getFirstname());
            }
            
        }

        $response = new Response(json_encode(Array('users'=>$returnvalues)));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response; 
        
    }
    
    
    /**
     * Lists all User entities.
     *
     * @Route("/", name="user")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_USER_LIST')")
     */
    public function indexAction() {
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamUserBundle:User');
        return $this->render('XxamUserBundle:User:index.js.twig', array('modelfields'=>$repository->getModelFields(),'gridcolumns'=>$repository->getGridColumns()));
    }
    
    /**
     * Show create form.
     *
     * @Route("/edit", name="user_new")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_USER_CREATE')")
     */
    public function newAction() {
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamUserBundle:User');
        $groups=$this->getDoctrine()->getManager()->getRepository('XxamUserBundle:Group')->findAll();
        $entity=new User();
        
        return $this->render('XxamUserBundle:User:edit.js.twig', array('entity'=>$entity,'groups'=>$groups,'roles'=>$this->getRolesFormatted($entity),'modelfields'=>$repository->getModelFields()));
    }
    
    
    
    /**
     * Show create form.
     *
     * @Route("/edit/{id}", name="user_edit")
     * @Method("GET")
     * @Template()
     * @Security("has_role('ROLE_USER_EDIT')")
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();
        /** @var UserRepository $repository */
        $repository=$em->getRepository('XxamUserBundle:User');

        $groups=$em->getRepository('XxamUserBundle:Group')->findAll();

        $entity = $repository->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }
        $form   = $this->createForm(new UserType($this->getRoles()), $entity)->createView();

        /** @var User $entity */

        return $this->render('XxamUserBundle:User:edit.js.twig', array('entity'=>$entity,'groups'=>$groups,'form'=>$form,'roles'=>$this->getRolesFormatted($entity),'modelfields'=>$repository->getModelFields()));
    }

    private function getRolesFormatted(User $entity){
        $returnarr=[];
        $roles=$this->getRoles();
        foreach($roles as $role){
            $ischecked=false;
            $isdisabled=false;
            if (in_array($role,$entity->getDirectroles())){
                $ischecked=true;
                $isdisabled=false;
            }else if (in_array($role,$entity->getRoles())){
                $ischecked=true;
                $isdisabled=true;
            }
            $returnarr[]=[
                "boxLabel"=> ucfirst(strtolower(str_replace('_',' ',substr($role,5)))),
                "name"=> 'roles',
                "inputValue"=>$role,
                "checked"=>$ischecked,
                'disabled'=>$isdisabled
            ];
        }
        return $returnarr;

    }
    
}
