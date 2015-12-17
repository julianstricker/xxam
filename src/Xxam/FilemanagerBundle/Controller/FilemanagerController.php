<?php

namespace Xxam\FilemanagerBundle\Controller;


use Xxam\FilemanagerBundle\Entity\Filesystem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Xxam\FilemanagerBundle\Entity\FilesystemRepository;


class FilemanagerController extends FilemanagerBaseController {

    /**
     * Filemanager Administration
     *
     * @Security("has_role('ROLE_FILEMANAGER_ADMIN')")
     */
    public function adminAction() {
        /** @var FilesystemRepository $repository */
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamFilemanagerBundle:Filesystem');
        return $this->render('XxamFilemanagerBundle:Filemanager:admin.js.twig', array('modelfields'=>$repository->getModelFields(),'gridcolumns'=>$repository->getGridColumns()));
    }
    
    /**
     * Filemanager.
     *
     * @Security("has_role('ROLE_FILEMANAGER_LIST')")
     */
    public function indexAction() {
        $fileextensionswiththumbnails=$this->container->getParameter('fileextensionswiththumbnails');
        $fileextensiontomimetype=$this->container->getParameter('fileextensiontomimetype');
        return $this->render('XxamFilemanagerBundle:Filemanager:index.js.twig', array('fileextensiontomimetype'=>$fileextensiontomimetype,'fileextensionswiththumbnails'=>$fileextensionswiththumbnails));
    }
    
    /**
     * Show create form.
     *
     * @Security("has_role('ROLE_FILEMANAGER_CREATE')")
     */
    public function newAction() {
        /** @var FilesystemRepository $repository */
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamFilemanagerBundle:Filesystem');
        $entity=new Filesystem();
        return $this->render('XxamFilemanagerBundle:Filemanager:edit.js.twig', array('entity'=>$entity,'modelfields'=>$repository->getModelFields()));
    }
    
    /**
     * Show create form.
     *
     * @Security("has_role('ROLE_FILEMANAGER_EDIT')")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();
        /** @var FilesystemRepository $repository */
        $repository=$em->getRepository('XxamFilemanagerBundle:Filesystem');

        $entity = $repository->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Filesystem entity.');
        }
        
        return $this->render('XxamFilemanagerBundle:Filemanager:edit.js.twig', array('entity'=>$entity,'modelfields'=>$repository->getModelFields(),'filesystemadapters'=>$this->container->getParameter('filesystemadapters')));
    }
    
}
