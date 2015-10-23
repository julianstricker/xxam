<?php

namespace Xxam\CmsAdminBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Xxam\CmsAdminBundle\Entity\CmsAdmin;
use Xxam\CmsAdminBundle\Form\Type\CmsAdminType;
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
        $posts = $dm->getRepository('XxamCmsAdminBundle:Post')->findAll();

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
        //$repository=$this->getDoctrine()->getManager()->getRepository('XxamCmsAdminBundle:CmsAdmin');
        return $this->render('XxamCmsAdminBundle:CmsAdmin:index.js.twig', array());

    }

    /**
     * Mailclient
     *
     * @Security("has_role('ROLE_MAILCLIENT_LIST')")
     *
     */
    public function listfoldersAction() {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        //Muss noch geändert werden:
        $workspaces=['default','xxam'];
        $repository = $this->get('doctrine_phpcr')->getRepository('XxamCmsAdminBundle:Page');
        $repository->setWorkspace('xxam');
        $qb = $repository->createQueryBuilder('page');
        $qb->where()->child('/cms/pages', 'page');
        $pages=$qb->getQuery()->execute();
        dump($pages);
        dump($pages['/cms/pages/Home']->getChildren());

        foreach($mailaccountusers as $mailaccountuser){
            $mailaccount=$mailaccountuser->getMailaccount();
            $mailbox = $this->getImapMailbox($mailaccount,'');
            $folders = $mailbox->getListingFolders();
            $children = Array();
            $mailaccountid=$mailaccount->getId();
            foreach ($folders as $folder) {
                $folderexpl = explode('.', trim($folder, '.'));
                $subfolder = &$children;
                $path=$mailaccountid;
                foreach ($folderexpl as $fexp) {
                    $path.='.'.$fexp;
                    if (!isset($subfolder['children'])) $subfolder['children']=Array();
                    if (!isset($subfolder['children'][$fexp])) {
                        $subfolder['children'][$fexp] = Array(
                            'text' => $fexp,
                            'path' => $path,
                            'loaded'=>true,
                            'expanded'=>true,
                            'leaf' => false,
                            'allowChildren' => true,
                        );
                        $subfolder['leaf']=false;
                        $subfolder['loaded']=false;
                        $subfolder['expanded']=false;
                        if ($mailaccountid.$mailaccount->getTrashfolder()==$path) $subfolder['children'][$fexp]['icon']='/bundles/xxammailclient/icons/16x16/bin.png';
                        if ($mailaccountid.$mailaccount->getJunkfolder()==$path) $subfolder['children'][$fexp]['icon']='/bundles/xxammailclient/icons/16x16/spam_assassin.png';
                        if ($mailaccountid.$mailaccount->getSentfolder()==$path) $subfolder['children'][$fexp]['icon']='/bundles/xxammailclient/icons/16x16/email_go.png';
                        if ($mailaccountid.$mailaccount->getDraftfolder()==$path) $subfolder['children'][$fexp]['icon']='/bundles/xxammailclient/icons/16x16/email_edit.png';
                    }
                    $subfolder = &$subfolder['children'][$fexp];
                }
            }
            $children['expanded']=true;
            $children['text']= $mailaccount->getAccountname();
            $children['path']=$mailaccountid;  //<-account_id


            $returndata[] =  $this->removeChildrenkeys($children);
        }
        //dump($returndata);
        //setLocale(LC_ALL,'de_DE.UTF8');
        $response = new Response(json_encode($returndata));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
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
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamCmsAdminBundle:CmsAdmin');
        $entity=new CmsAdmin();
        return $this->render('XxamCmsAdminBundle:CmsAdmin:edit.js.twig', array('entity'=>$entity,'cmsadmintypes'=>$this->cmsadmintypesAsKeyValue(),'modelfields'=>$repository->getModelFields()));
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
        $repository=$this->getDoctrine()->getManager()->getRepository('XxamCmsAdminBundle:CmsAdmin');

        $entity = $repository->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find CmsAdmin entity.');
        }
        $cmsadmintypes=Array();
        foreach($this->container->getParameter('cmsadmintypes') as $key => $value){
            $cmsadmintypes[]=Array('id'=>$key,'value'=>$value);
        }
        return $this->render('XxamCmsAdminBundle:CmsAdmin:edit.js.twig', array('entity'=>$entity,'cmsadmintypes'=>$this->cmsadmintypesAsKeyValue(),'modelfields'=>$repository->getModelFields()));
    }

    
}
