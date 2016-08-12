<?php

namespace Xxam\MailclientBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Xxam\MailclientBundle\Entity\Mailspool;
use Xxam\MailclientBundle\Entity\MailspoolRepository;


/**
 * Mailspool controller.
 *
 * @Route("/mailspool")
 */
class MailspoolController extends Controller
{
    /**
     * Lists all Mailspool entities.
     *
     * @Route("/", name="mailspool_index")
     * @Method("GET")
     * @Security("has_role('ROLE_MAILCLIENT_LIST')")
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();

        /** @var MailspoolRepository $repository */
        $repository=$em->getRepository('XxamMailclientBundle:Mailspool');
        return $this->render('XxamMailclientBundle:Mailclient:mailspool.js.twig', array('modelfields'=>$repository->getModelFields(),'gridcolumns'=>$repository->getGridColumns()));
    }

    /**
     * Creates a new Mailspool entity.
     *
     * @Route("/new", name="mailspool_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $mailspool = new Mailspool();
        $form = $this->createForm('Xxam\MailclientBundle\Form\MailspoolType', $mailspool);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($mailspool);
            $em->flush();

            return $this->redirectToRoute('mailspool_show', array('id' => $mailspool->getId()));
        }

        return $this->render('mailspool/new.html.twig', array(
            'mailspool' => $mailspool,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Mailspool entity.
     *
     * @Route("/{id}", name="mailspool_show")
     * @Method("GET")
     */
    public function showAction(Mailspool $mailspool)
    {
        $deleteForm = $this->createDeleteForm($mailspool);

        return $this->render('mailspool/show.html.twig', array(
            'mailspool' => $mailspool,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Mailspool entity.
     *
     * @Route("/{id}/edit", name="mailspool_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Mailspool $mailspool)
    {
        $deleteForm = $this->createDeleteForm($mailspool);
        $editForm = $this->createForm('Xxam\MailclientBundle\Form\MailspoolType', $mailspool);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($mailspool);
            $em->flush();

            return $this->redirectToRoute('mailspool_edit', array('id' => $mailspool->getId()));
        }

        return $this->render('mailspool/edit.html.twig', array(
            'mailspool' => $mailspool,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Mailspool entity.
     *
     * @Route("/{id}", name="mailspool_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Mailspool $mailspool)
    {
        $form = $this->createDeleteForm($mailspool);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($mailspool);
            $em->flush();
        }

        return $this->redirectToRoute('mailspool_index');
    }

    /**
     * Creates a form to delete a Mailspool entity.
     *
     * @param Mailspool $mailspool The Mailspool entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Mailspool $mailspool)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('mailspool_delete', array('id' => $mailspool->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
