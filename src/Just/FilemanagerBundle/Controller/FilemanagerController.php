<?php

namespace Just\FilemanagerBundle\Controller;


use Just\FilemanagerBundle\Entity\Filesystem;


class FilemanagerController extends FilemanagerBaseController {

    /**
     * Filemanager Administration
     *
     * @Security("has_role('ROLE_FILEMANAGER_ADMIN')")
     */
    public function adminAction() {
        $repository=$this->getDoctrine()->getManager()->getRepository('JustFilemanagerBundle:Filesystem');
        return $this->render('JustFilemanagerBundle:Filemanager:admin.js.twig', array('modelfields'=>$repository->getModelFields(),'gridcolumns'=>$repository->getGridColumns()));
    }
    
    /**
     * Filemanager.
     *
     * @Security("has_role('ROLE_FILEMANAGER_LIST')")
     */
    public function indexAction() {
        $fileextensionswiththumbnails=$this->container->getParameter('fileextensionswiththumbnails');
        $fileextensiontomimetype=$this->container->getParameter('fileextensiontomimetype');
        return $this->render('JustFilemanagerBundle:Filemanager:index.js.twig', array('fileextensiontomimetype'=>$fileextensiontomimetype,'fileextensionswiththumbnails'=>$fileextensionswiththumbnails));
    }
    
    /**
     * Show create form.
     *
     * @Security("has_role('ROLE_FILEMANAGER_CREATE')")
     */
    public function newAction() {
        $repository=$this->getDoctrine()->getManager()->getRepository('JustFilemanagerBundle:Filesystem');
        $entity=new Filesystem();
        return $this->render('JustFilemanagerBundle:Filesystem:edit.js.twig', array('entity'=>$entity,'modelfields'=>$repository->getModelFields()));
    }
    
    /**
     * Show create form.
     *
     * @Security("has_role('ROLE_FILEMANAGER_EDIT')")
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();
        $repository=$em->getRepository('JustFilemanagerBundle:Filesystem');

        $entity = $repository->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Filesystem entity.');
        }
        
        return $this->render('JustFilemanagerBundle:Filemanager:edit.js.twig', array('entity'=>$entity,'modelfields'=>$repository->getModelFields()));
    }
    
}
