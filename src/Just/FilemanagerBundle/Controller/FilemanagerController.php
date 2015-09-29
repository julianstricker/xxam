<?php

namespace Just\FilemanagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Dropbox\DropboxAdapter;
use Dropbox\Client as DropboxClient;
use League\Flysystem\Adapter\Ftp as FtpAdapter;
use League\Flysystem\Sftp\SftpAdapter;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Cached\Storage\Memcached as Cache;
use League\Flysystem\Plugin\ListWith;
use League\Flysystem\Plugin\ListPaths;
use League\Flysystem\MountManager;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\NotSupportedException;
use League\Flysystem\RootViolationException;

use Just\FilemanagerBundle\Helper\FlysystemPlugins\ThumbnailDropbox;

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
        $repository=$this->getDoctrine()->getManager()->getRepository('JustFilemanagerBundle:Filesystem');

        $entity = $repository->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Filesystem entity.');
        }
        
        return $this->render('JustFilemanagerBundle:Filemanager:edit.js.twig', array('entity'=>$entity,'modelfields'=>$repository->getModelFields()));
    }
    
}
