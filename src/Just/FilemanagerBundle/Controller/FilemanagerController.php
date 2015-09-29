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

class FilemanagerController extends Controller {

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
    
    private function removeChildrenkeys($children){
        if (isset($children['children'])){
            $children['children']=array_values($children['children']);
            foreach($children['children'] as $key => $child){
                $children['children'][$key] = $this->removeChildrenkeys($child);
            }
        }
        return $children;
    }
    
    private function getFilesystems(){
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        
        $filesystems=Array();
        $fss=$user->getFilesystems();
        if (count($fss)>0){
            foreach($fss as $filesystem){
                $filesystems[$filesystem->getId()]=$filesystem;
            }
        }
        if (count($user->getGroups())>0){
            foreach($user->getGroups() as $group){
                $fss=$group->getFilesystems();
                if (count($fss)>0){
                    foreach($fss as $filesystem){
                        $filesystems[$filesystem->getId()]=$filesystem;
                    }
                }
            }
        }
        return $filesystems;
    }
    
    private function createLocalAdapter($settings)
    {
        $adapter = new Local($settings['path'],$settings['locks']); //,constant('Local::'.$settings['links']));
        return $adapter;
    }
    
    private function createDropboxAdapter($settings)
    {
        $client = new DropboxClient($settings['access_token'],$settings['app_secret']); //,constant('Local::'.$settings['links']));
        $adapter = new DropboxAdapter($client, $settings['prefix']);

        return $adapter;
    }
    
    private function createFtpAdapter($settings)
    {
        $adapter = new FtpAdapter($settings);
        return $adapter;
    }
    
    private function createSftpAdapter($settings)
    {
        $adapter = new SftpAdapter($settings);
        return $adapter;
    }
    
    private function getFs($filesystem){
        $fs=false;
        $settings=json_decode($filesystem->getSettings(),true);
        switch ($filesystem->getAdapter()) {
            case 'local':
                $adapter=$this->createLocalAdapter($settings);
                break;
            case 'dropbox':
                $adapter=$this->createDropboxAdapter($settings);
                break;
            case 'ftp':
                $adapter=$this->createFtpAdapter($settings);
                break;
            case 'sftp':
                $adapter=$this->createSftpAdapter($settings);
                break;
        }
        if(!$adapter) return false;
        if ($settings['cache']){
            $memcached = new \Memcached;
            $memcached->addServer('localhost', 11211);

            $cadapter = new CachedAdapter(
                $adapter,
                new Cache($memcached, 'xxam_filemanager_'.$filesystem->getId(), 300)
            );
            $fs = new Filesystem($cadapter);
        }else{
            $fs = new Filesystem($adapter);
        }
        switch ($filesystem->getAdapter()) {
            case 'local':
                
                break;
            case 'dropbox':
                $fs->addPlugin(new ThumbnailDropbox);
                break;
            
        }
        //$fs->addPlugin(new ListWith);
        return $fs;
    }
    
    public function listfoldersAction(Request $request) {
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        $path=$request->get('path','');
        $filesystemid=false;
        if ($path!=''){
            $pathexpl=explode('/',$path);
            $filesystemid=$pathexpl[0];
            unset($pathexpl[0]);
            $path=count($pathexpl)>0 ? '/'.implode('/',$pathexpl) : '';
        }
        $returndata=Array('success'=>true, 'children'=>Array());
        $filesystems=$this->getFilesystems();
        if (count($filesystems)==0){
            return $this->throwJsonError('No Filesystem found');
        }elseif($filesystemid==false){
            foreach($filesystems as $filesystem){
                 $returndata['children'][]=Array(
                    'id'=>(string)$filesystem->getId(),
                    'basename'=>(string)$filesystem->getId(),
                    'name'=>$filesystem->getFilesystemname(),
                    'icon'=>$filesystem->getUser() ? '/bundles/justadmin/icons/16x16/folder_user.png' : '/bundles/justadmin/icons/16x16/folder.png',
                    'leaf'=>false
                );
            }
        }else{
            $filesystem=isset($filesystems[$filesystemid]) ? $filesystems[$filesystemid] : false;
            if (!$filesystem){
                return $this->throwJsonError('Filesystem not found');
            }
            $fs=$this->getFs($filesystem);
            $contents = $fs->listContents($path);
            //dump($contents);
            foreach($contents as $content){
                if ($content['type']=='dir'){
                    $returndata['children'][]=Array(
                        'id'=>(string)$filesystem->getId().'/'.$content['path'],
                        'basename'=>$content['basename'],
                        'name'=>$content['basename'],
                        'icon'=> '/bundles/justadmin/icons/16x16/folder.png',
                        'timestamp'=>$content['timestamp']
                    );
                }
            }

        }
        
        
        $response = new Response(json_encode($returndata));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }
    public function listfilesAction(Request $request){
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        $path=$request->get('path','');
        $refresh=$request->get('refresh',false);
        $filesystemid=false;
        if ($path!=''){
            $pathexpl=explode('/',$path);
            $filesystemid=$pathexpl[0];
            unset($pathexpl[0]);
            $path=count($pathexpl)>0 ? '/'.implode('/',$pathexpl) : '';
        }
        $returndata=Array('success'=>true, 'children'=>Array());
        $filesystems=$this->getFilesystems();
        if (count($filesystems)==0){
            return $this->throwJsonError('No Filesystem found');
        }elseif($filesystemid==false){
            foreach($filesystems as $filesystem){
                
                $returndata['children'][]=Array(
                    'id'=>(string)$filesystem->getId(),
                    'name'=>$filesystem->getFilesystemname(),
                    'timestamp'=>null,
                    'type'=>$filesystem->getUser() ? 'privatefs' : 'fs',
                    'size'=>null
                );
            }
        }else{
            $filesystem=isset($filesystems[$filesystemid]) ? $filesystems[$filesystemid] : false;
            if (!$filesystem){
                return $this->throwJsonError('Filesystem not found');
            }
            $fs=$this->getFs($filesystem);
            //if ($refresh) $fs->getAdapter()->flush();
            $contents = $fs->listContents($path);
            //$contents = $fs->listWith(['mimetype', 'size', 'timestamp'], $path, true);

            //dump($contents);
            foreach($contents as $content){
                
                $returndata['children'][]=Array(
                    'id'=>(string)$filesystem->getId().'/'.$content['path'],
                    'name'=>$content['basename'],
                    'timestamp'=>$content['timestamp'],
                    'type'=>$content['type'],
                    'size'=>$content['size'],
                    //'thumbnail'=>$fs->getThumbnail($content['path'],'s')
                );
                
            }

        }
        
        
        $response = new Response(json_encode($returndata));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }
    public function getThumbnailAction(Request $request){
        $this->memcached = new \Memcached;
        $this->memcached->addServer('localhost', 11211);
        $fileextensionswiththumbnails=$this->container->getParameter('fileextensionswiththumbnails');
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        $tenant_id=$request->getSession()->get('tenant_id', 1);
        $originalpath=$request->get('path','');
        $path=$originalpath;
        $size=$request->get('size','s');
        $filesystemid=false;
        if ($path!=''){
            $pathexpl=explode('/',$path);
            $filesystemid=$pathexpl[0];
            unset($pathexpl[0]);
            $path=count($pathexpl)>0 ? '/'.implode('/',$pathexpl) : '';
        }
        $ctime=new \DateTime();
        $cachename= 'JustFilemanagerBundleThumbnails'. $tenant_id .$size.md5($originalpath);
        //$this->memcached->delete($cachename);
        $filesystems=$this->getFilesystems();
        if (count($filesystems)==0){
            return $this->throwJsonError('No Filesystem found');
        }else{
            $filesystem=isset($filesystems[$filesystemid]) ? $filesystems[$filesystemid] : false;
            if (!$filesystem){
                return $this->throwJsonError('Filesystem not found');
            }
            $fs=$this->getFs($filesystem);
            $pathexpl=explode('.',$path);
            $fileextension=strtolower($pathexpl[count($pathexpl)-1]);
            if($size=='xxs' || !in_array($fileextension,$fileextensionswiththumbnails)){
                $response= new Response($this->getIcon($path,$size));
            }else{
                $timestamp = $fs->getTimestamp($path);
                $fromcache = $this->getImageFromCache($cachename,$timestamp);
                if ($fromcache) { //ist bereits im cache:
                    $thumbnaildata = $fromcache;
                    $ctime=new \DateTime();
                    $ctime->setTimestamp(intval($thumbnaildata[0]));
                    $response = new Response($thumbnaildata[1]);
                } else {
                    $thumbnaildata = $fs->getThumbnail($path,$size);
                    if ($thumbnaildata[1]){
                        $thumbnaildata[0]=$ctime->getTimestamp();
                        $this->memcached->set($cachename, serialize($ctime->getTimestamp().'|'.$thumbnaildata[1]));
                        $response = new Response($thumbnaildata[1]);
                    }else{ //icon ausgeben:
                        $response= new Response($this->getIcon($path,$size));


                    }
                    //echo 'new';

                //dump($thumbnaildata);
                }
            }
        }
        
        
        $expires = 1 * 24 * 60 * 60;
        
        $response->headers->set('Content-Length', strlen($thumbnaildata[1]));
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Last-Modified', $ctime->format('D, d M Y H:i:s') . ' GMT');
        $response->headers->set('Cache-Control', 'maxage=' . $expires);
        $response->headers->set('Content-Type', 'image/png; charset=UTF-8');
        $response->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
        return $response;
    }
    
    private function getIcon($path,$size){
        $pathexpl=explode('.',$path);
        $fileextension=strtolower($pathexpl[count($pathexpl)-1]);
        $kernel = $this->get('kernel');
        $fileextensiontomimetype=$this->container->getParameter('fileextensiontomimetype');
        if(isset($fileextensiontomimetype[$fileextension])){
            $iconpath = $kernel->locateResource('@JustAdminBundle/Resources/public/icons/'.(isset($this->thumbnailsizes[$size]) ? $this->thumbnailsizes[$size] : '64x64').'/mimetypes/'.$fileextensiontomimetype[$fileextension]);
            return file_get_contents($iconpath);
        }
        $iconpath = $kernel->locateResource('@JustAdminBundle/Resources/public/icons/'.(isset($this->thumbnailsizes[$size]) ? $this->thumbnailsizes[$size] : '64x64').'/mimetypes/unknown.png');
        return file_get_contents($iconpath);
    }
    
    private function getImageFromCache($cachename,$timestamp) {
        $cachefile = unserialize($this->memcached->get($cachename));
        if ($cachefile) {
            //ist bereits im cache:
            $cachefile=explode('|',$cachefile,2);
            
            
            if (!intval($cachefile[0]) || $timestamp>intval($cachefile[0])){
                $this->memcached->delete($cachename);
                return false;
            }
            return $cachefile;
        }
        return false;
    }
    
    public function deletefileAction(Request $request){
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        $path=$request->get('path','');
        $filesystemid=false;
        if ($path!=''){
            $pathexpl=explode('/',$path);
            $filesystemid=$pathexpl[0];
            unset($pathexpl[0]);
            $path=count($pathexpl)>0 ? '/'.implode('/',$pathexpl) : '';
        }
        $returndata=Array('success'=>false);
        $filesystems=$this->getFilesystems();
        if (count($filesystems)==0){
            return $this->throwJsonError('No Filesystem found');
        }else{
            $filesystem=isset($filesystems[$filesystemid]) ? $filesystems[$filesystemid] : false;
            if (!$filesystem){
                return $this->throwJsonError('Filesystem not found');
            }
            $fs=$this->getFs($filesystem);
            if ($fs->has($path)){
                $metadata = $fs->getMetadata($path);
                if ($metadata['type']=='dir'){
                    $fs->deleteDir($path);
                }else{
                    $fs->delete($path);
                }
                $returndata=Array('success'=>true,'metadata'=>$metadata);
            }
        }
        $response = new Response(json_encode($returndata));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }
    
    public function movefileAction(Request $request){
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        $path=$request->get('path','');
        $newname=$request->get('newname','');
        $filesystemid=false;
        if ($path!=''){
            $pathexpl=explode('/',$path);
            $filesystemid=$pathexpl[0];
            unset($pathexpl[0]);
            $path=count($pathexpl)>0 ? '/'.implode('/',$pathexpl) : '';
        }
        if ($newname!=''){
            $newnameexpl=explode('/',$newname);
            $filesystemid2=$newnameexpl[0];
            unset($newnameexpl[0]);
            $newname=count($newnameexpl)>0 ? '/'.implode('/',$newnameexpl) : '';
        }
        $returndata=Array('success'=>false);
        $filesystems=$this->getFilesystems();
        if (count($filesystems)==0){
            return $this->throwJsonError('No Filesystem found');
        }else{
            $filesystem=isset($filesystems[$filesystemid]) ? $filesystems[$filesystemid] : false;
            if (!$filesystem){
                return $this->throwJsonError('Filesystem not found');
            }
            $fs=$this->getFs($filesystem);
            if ($fs->has($path)){
                $metadata = $fs->getMetadata($path);
                if ($filesystemid==$filesystemid2){
                    try {
                        $resp=$fs->rename($path,$newname);
                    } catch (FileExistsException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (FileNotFoundException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (NotSupportedException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (RootViolationException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (Exception $e) {
                        return $this->throwJsonError($e->getMessage());
                    }
                }else{
                    $filesystem2=isset($filesystems[$filesystemid2]) ? $filesystems[$filesystemid2] : false;
                    if (!$filesystem2){
                        return $this->throwJsonError('Filesystem not found');
                    }
                    $fs2=$this->getFs($filesystem2);
                    
                    $manager = new MountManager([
                        'fssource' => $fs,
                        'fsdestination' => $fs2,
                    ]);
                    try {
                        $manager->move('fssource:/'.$path, 'fsdestination:/'.$newname);
                    } catch (FileExistsException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (FileNotFoundException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (NotSupportedException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (RootViolationException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (Exception $e) {
                        return $this->throwJsonError($e->getMessage());
                    }
                }
                $returndata=Array('success'=>true,'resp'=>$resp);
            }
        }
        $response = new Response(json_encode($returndata));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }
    
    public function copyfileAction(Request $request){
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        $path=$request->get('path','');
        $newname=$request->get('newname','');
        $filesystemid=false;
        if ($path!=''){
            $pathexpl=explode('/',$path);
            $filesystemid=$pathexpl[0];
            unset($pathexpl[0]);
            $path=count($pathexpl)>0 ? '/'.implode('/',$pathexpl) : '';
        }
        if ($newname!=''){
            $newnameexpl=explode('/',$newname);
            $filesystemid2=$newnameexpl[0];
            unset($newnameexpl[0]);
            $newname=count($newnameexpl)>0 ? '/'.implode('/',$newnameexpl) : '';
        }
        $returndata=Array('success'=>false);
        $filesystems=$this->getFilesystems();
        if (count($filesystems)==0){
            return $this->throwJsonError('No Filesystem found');
        }else{
            $filesystem=isset($filesystems[$filesystemid]) ? $filesystems[$filesystemid] : false;
            if (!$filesystem){
                return $this->throwJsonError('Filesystem not found');
            }
            $fs=$this->getFs($filesystem);
            if ($fs->has($path)){
                $metadata = $fs->getMetadata($path);
                if ($filesystemid==$filesystemid2){
                    try {
                        $resp=$fs->copy($path,$newname);
                    } catch (FileExistsException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (FileNotFoundException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (NotSupportedException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (RootViolationException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (Exception $e) {
                        return $this->throwJsonError($e->getMessage());
                    }
                }else{
                    $filesystem2=isset($filesystems[$filesystemid2]) ? $filesystems[$filesystemid2] : false;
                    if (!$filesystem2){
                        return $this->throwJsonError('Filesystem not found');
                    }
                    $fs2=$this->getFs($filesystem2);
                    
                    $manager = new MountManager([
                        'fssource' => $fs,
                        'fsdestination' => $fs2,
                    ]);
                    try {
                        $manager->copy('fssource:/'.$path, 'fsdestination:/'.$newname);
                    } catch (FileExistsException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (FileNotFoundException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (NotSupportedException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (RootViolationException $e) {
                        return $this->throwJsonError($e->getMessage());
                    } catch (Exception $e) {
                        return $this->throwJsonError($e->getMessage());
                    }
                }
                $returndata=Array('success'=>true,'resp'=>$resp);
            }
        }
        $response = new Response(json_encode($returndata));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }
    
    public function writefileAction(Request $request){
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        $path=$request->get('path','');
        $content=$request->get('content','');
        $filesystemid=false;
        if ($path!=''){
            $pathexpl=explode('/',$path);
            $filesystemid=$pathexpl[0];
            unset($pathexpl[0]);
            $path=count($pathexpl)>0 ? '/'.implode('/',$pathexpl) : '';
        }
        $returndata=Array('success'=>false);
        $filesystems=$this->getFilesystems();
        if (count($filesystems)==0){
            return $this->throwJsonError('No Filesystem found');
        }else{
            $filesystem=isset($filesystems[$filesystemid]) ? $filesystems[$filesystemid] : false;
            if (!$filesystem){
                return $this->throwJsonError('Filesystem not found');
            }
            $fs=$this->getFs($filesystem);
            $fs->put($path, $content);
            $returndata=Array('success'=>true,'metadata'=>$metadata);
            
        }
        $response = new Response(json_encode($returndata));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }
    
    public function readfileAction(Request $request){
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        $path=$request->get('path','');
        $filesystemid=false;
        if ($path!=''){
            $pathexpl=explode('/',$path);
            $filesystemid=$pathexpl[0];
            unset($pathexpl[0]);
            $path=count($pathexpl)>0 ? '/'.implode('/',$pathexpl) : '';
        }
        $returndata=Array('success'=>false);
        $filesystems=$this->getFilesystems();
        if (count($filesystems)==0){
            return $this->throwJsonError('No Filesystem found');
        }else{
            $filesystem=isset($filesystems[$filesystemid]) ? $filesystems[$filesystemid] : false;
            if (!$filesystem){
                return $this->throwJsonError('Filesystem not found');
            }
            $fs=$this->getFs($filesystem);
            if ($fs->has($path)){
                $metadata = $fs->getMetadata($path);
                $content=$fs->read($path);
                $response = new Response($content);
                if ($metadata['mimetype']) $response->headers->set('Content-Type', $metadata['mimetype']);
                if ($metadata['size']) $response->headers->set('Content-Length', $metadata['size']);
                $response->headers->set('Content-disposition', 'inline; filename="'.$metadata['basename'].'"');
                return $response;
            }
        }
        $response = new Response(json_encode($returndata));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }
    
    private function throwJsonError($errormessage) {
        $response = new Response(json_encode(Array('success'=>'false','error' => $errormessage)));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }
    
    private $thumbnailsizes=Array(
        "xxs"=>"32x32",
        "xs"=>"32x32",
        "s"=>"64x64",
        "m"=>"128x128",
    );
    
}
