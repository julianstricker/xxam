<?php

namespace Xxam\UserBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Xxam\UserBundle\Entity\Group;
use Xxam\UserBundle\Entity\GroupRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Group controller.
 *
 * @Route("/group")
 */
class GroupController extends Controller
{
    use Base\UserTrait;

    /**
     * Search groups for autocomplete box.
     *
     * @Route("/searchgroups", name="group_searchgroups")
     * @Method("GET")
     *
     * @param Request $request
     * @return Response
     */
    public function searchgroupsAction(Request $request)
    {
        
        $queryparam = $request->get('query');

        $returnvalues=Array();
        $em = $this->getDoctrine()->getManager();
        /** @var GroupRepository $repository */
        $repository=$em->getRepository('XxamUserBundle:Group');
        $query = $repository->createQueryBuilder('e');
        $query->andWhere('(e.lastname LIKE :queryparam1 OR e.firstname LIKE :queryparam2)');
        $query->setParameter('queryparam1', '%'.$queryparam.'%');
        $query->setParameter('queryparam2', '%'.$queryparam.'%');
        $entities = $query->getQuery()->getResult();
        if ($entities) {

            /** @var Group $entity */
            foreach($entities as $entity){
                $returnvalues[]=Array('value'=>$entity->getName());
            }
            
        }

        $response = new Response(json_encode(Array('groups'=>$returnvalues)));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response; 
        
    }
    
    
    /**
     * Lists all Group entities.
     *
     * @Route("/", name="group")
     * @Method("GET")
     */
    public function indexAction() {
        /** @var GroupRepository $repository */
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamUserBundle:Group');
        return $this->render('XxamUserBundle:Group:index.js.twig', array('modelfields'=>$repository->getModelFields(),'gridcolumns'=>$repository->getGridColumns()));
    }
    
    /**
     * Show create form.
     *
     * @Route("/edit", name="group_new")
     * @Method("GET")
     */
    public function newAction() {
        /** @var GroupRepository $repository */
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamUserBundle:Group');
        $entity=new Group('',$this->getRoles());
        return $this->render('XxamUserBundle:Group:edit.js.twig', array('entity'=>$entity,'roles'=>$this->getRolesFormatted($entity),'modelfields'=>$repository->getModelFields()));
    }

    /**
     * Show create form.
     *
     * @Route("/edit/{id}", name="group_edit")
     * @Method("GET")
     * @Security("has_role('ROLE_GROUP_EDIT')")
     * @param $id
     * @return Response
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();
        /** @var GroupRepository $repository */
        $repository=$em->getRepository('XxamUserBundle:Group');
        
        $entity = $repository->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Group entity.');
        }
        /** @var Group $entity */
        return $this->render('XxamUserBundle:Group:edit.js.twig', array('entity'=>$entity,'roles'=>$this->getRolesFormatted($entity),'modelfields'=>$repository->getModelFields()));
    }

    private function getRolesFormatted(Group $entity){
        $returnarr=[];
        $roles=$this->getRoles();
        foreach($roles as $role){
            $returnarr[]=[
                "boxLabel"=> ucfirst(strtolower(str_replace('_',' ',substr($role,5)))),
                "name"=> 'roles',
                "inputValue"=>$role,
                "checked"=>(!empty($entity->getRoles()) && in_array($role,$entity->getRoles())) ? true : false
            ];
        }
        return $returnarr;

    }
    
    /**
     * Creates a new Group entity.
     *
     * @Route("/", name="group_create")
     * @Method("POST")
     * @Template("XxamUserBundle:Group:new.html.twig")
     */
//    public function createAction(Request $request)
//    {
//        $entity = new Group();
//        $form = $this->createCreateForm($entity);
//        $form->handleRequest($request);
//
//        if ($form->isValid()) {
//            $em = $this->getDoctrine()->getManager();
//            $em->persist($entity);
//            $em->flush();
//
//            return $this->redirect($this->generateUrl('group_show', array('id' => $entity->getId())));
//        }
//
//        return array(
//            'entity' => $entity,
//            'form'   => $form->createView(),
//        );
//    }

    /**
     * Creates a form to create a Group entity.
     *
     * @param Group $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
//    private function createCreateForm(Group $entity)
//    {
//        $form = $this->createForm(new GroupType(), $entity, array(
//            'action' => $this->generateUrl('group_create'),
//            'method' => 'POST',
//        ));
//
//        $form->add('submit', 'submit', array('label' => 'Create'));
//
//        return $form;
//    }

    /**
     * Displays a form to create a new Group entity.
     *
     * @Route("/new", name="group_new")
     * @Method("GET")
     * @Template()
     */
//    public function newAction()
//    {
//        $entity = new Group();
//        $form   = $this->createCreateForm($entity);
//
//        return array(
//            'entity' => $entity,
//            'form'   => $form->createView(),
//        );
//    }

    /**
     * Finds and displays a Group entity.
     *
     * @Route("/{id}", name="group_show")
     * @Method("GET")
     * @Template()
     */
//    public function showAction($id)
//    {
//        $em = $this->getDoctrine()->getManager();
//
//        $entity = $em->getRepository('XxamUserBundle:Group')->find($id);
//
//        if (!$entity) {
//            throw $this->createNotFoundException('Unable to find Group entity.');
//        }
//
//        $deleteForm = $this->createDeleteForm($id);
//
//        return array(
//            'entity'      => $entity,
//            'delete_form' => $deleteForm->createView(),
//        );
//    }

    /**
     * Displays a form to edit an existing Group entity.
     *
     * @Route("/{id}/edit", name="group_edit")
     * @Method("GET")
     * @Template()
     */
//    public function editAction($id)
//    {
//        $em = $this->getDoctrine()->getManager();
//
//        $entity = $em->getRepository('XxamUserBundle:Group')->find($id);
//
//        if (!$entity) {
//            throw $this->createNotFoundException('Unable to find Group entity.');
//        }
//
//        $editForm = $this->createEditForm($entity);
//        $deleteForm = $this->createDeleteForm($id);
//
//        return array(
//            'entity'      => $entity,
//            'edit_form'   => $editForm->createView(),
//            'delete_form' => $deleteForm->createView(),
//        );
//    }

    /**
    * Creates a form to edit a Group entity.
    *
    * @param Group $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
//    private function createEditForm(Group $entity)
//    {
//        $form = $this->createForm(new GroupType(), $entity, array(
//            'action' => $this->generateUrl('group_update', array('id' => $entity->getId())),
//            'method' => 'PUT',
//        ));
//
//        $form->add('submit', 'submit', array('label' => 'Update'));
//
//        return $form;
//    }
    /**
     * Edits an existing Group entity.
     *
     * @Route("/{id}", name="group_update")
     * @Method("PUT")
     * @Template("XxamUserBundle:Group:edit.html.twig")
     */
//    public function updateAction(Request $request, $id)
//    {
//        $em = $this->getDoctrine()->getManager();
//
//        $entity = $em->getRepository('XxamUserBundle:Group')->find($id);
//
//        if (!$entity) {
//            throw $this->createNotFoundException('Unable to find Group entity.');
//        }
//
//        $deleteForm = $this->createDeleteForm($id);
//        $editForm = $this->createEditForm($entity);
//        $editForm->handleRequest($request);
//
//        if ($editForm->isValid()) {
//            $em->flush();
//
//            return $this->redirect($this->generateUrl('group_edit', array('id' => $id)));
//        }
//
//        return array(
//            'entity'      => $entity,
//            'edit_form'   => $editForm->createView(),
//            'delete_form' => $deleteForm->createView(),
//        );
//    }
    /**
     * Deletes a Group entity.
     *
     * @Route("/{id}", name="group_delete")
     * @Method("DELETE")
     */
//    public function deleteAction(Request $request, $id)
//    {
//        $form = $this->createDeleteForm($id);
//        $form->handleRequest($request);
//
//        if ($form->isValid()) {
//            $em = $this->getDoctrine()->getManager();
//            $entity = $em->getRepository('XxamUserBundle:Group')->find($id);
//
//            if (!$entity) {
//                throw $this->createNotFoundException('Unable to find Group entity.');
//            }
//
//            $em->remove($entity);
//            $em->flush();
//        }
//
//        return $this->redirect($this->generateUrl('group'));
//    }

    /**
     * Creates a form to delete a Group entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
//    private function createDeleteForm($id)
//    {
//        return $this->createFormBuilder()
//            ->setAction($this->generateUrl('group_delete', array('id' => $id)))
//            ->setMethod('DELETE')
//            ->add('submit', 'submit', array('label' => 'Delete'))
//            ->getForm()
//        ;
//    }
    
    
}
