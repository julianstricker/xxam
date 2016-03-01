<?php

namespace Xxam\FilemanagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Dropbox\DropboxAdapter;
use Dropbox\Client as DropboxClient;
use League\Flysystem\Adapter\Ftp as FtpAdapter;
use League\Flysystem\Sftp\SftpAdapter;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Cached\Storage\Memcached as Cache;


use Xxam\FilemanagerBundle\Helper\FlysystemPlugins\ThumbnailDropbox;
use Xxam\UserBundle\Entity\Group;
use Xxam\FilemanagerBundle\Entity\Filesystem as XxamFilesystem;

class FilemanagerBaseController extends Controller {

    protected $thumbnailsizes=Array(
        "xxs"=>"32x32",
        "xs"=>"32x32",
        "s"=>"64x64",
        "m"=>"128x128",
    );

    /* @var \Memcached $memcached */
    protected  $memcached;
    
    protected function removeChildrenkeys($children){
        if (isset($children['children'])){
            $children['children']=array_values($children['children']);
            foreach($children['children'] as $key => $child){
                $children['children'][$key] = $this->removeChildrenkeys($child);
            }
        }
        return $children;
    }

    /**
     * @return array
     */
    protected function getFilesystems(){
        $token= $this->get('security.token_storage')->getToken();
        $user = $token->getUser();
        $filesystems=Array();
        $fss=$user->getFilesystems();
        if (count($fss)>0){

            foreach($fss as $filesystem){
                /** @var XxamFilesystem $filesystem */
                $filesystems[$filesystem->getId()]=$filesystem;
            }
        }
        if (count($user->getGroups())>0){
            foreach($user->getGroups() as $group){
                /** @var Group $group */
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

    protected function getFilesystemForPath($path)
    {
        $filesystemid=$this->extractFilesystemIdFromPath($path);
        $filesystems = $this->getFilesystems();
        if (count($filesystems) == 0) {
            return false;
        }
        $filesystem = isset($filesystems[$filesystemid]) ? $filesystems[$filesystemid] : false;
        return $filesystem;
    }

    /*
    * Extract Filesystem-Id from parameter "path"
    */
    protected function extractFilesystemIdFromPath($path)
    {
        if ($path != '') {
            $pathexpl = explode('/', $path);
            $filesystemid = $pathexpl[0];
            return $filesystemid;
        }
        return false;
    }

    /*
     * Extract Path part from parameter "path"
     */
    protected function extractPathFromPath($path)
    {
        if ($path != '') {
            $pathexpl = explode('/', $path);
            unset($pathexpl[0]);
            $path = count($pathexpl) > 0 ? '/' . implode('/', $pathexpl) : '';
        }
        return $path;
    }
    
    protected function createLocalAdapter($settings)
    {
        $adapter = new Local($settings['path'],$settings['locks']); //,constant('Local::'.$settings['links']));
        return $adapter;
    }
    
    protected function createDropboxAdapter($settings)
    {
        $client = new DropboxClient($settings['access_token'],$settings['app_secret']); //,constant('Local::'.$settings['links']));
        $adapter = new DropboxAdapter($client, $settings['prefix']);

        return $adapter;
    }
    
    protected function createFtpAdapter($settings)
    {
        $adapter = new FtpAdapter($settings);
        return $adapter;
    }
    
    protected function createSftpAdapter($settings)
    {
        $adapter = new SftpAdapter($settings);
        return $adapter;
    }
    
    protected function getFs( XxamFilesystem $filesystem){
        $settings=json_decode($filesystem->getSettings(),true);
        $adapter=false;
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
        if (!empty($settings['cache']) && $settings['cache']){
            $memcached = $this->get('memcached');
            dump($memcached);
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
    
    protected function getIcon($path,$size){
        $pathexpl=explode('.',$path);
        $fileextension=strtolower($pathexpl[count($pathexpl)-1]);
        $kernel = $this->get('kernel');
        $fileextensiontomimetype=$this->container->getParameter('fileextensiontomimetype');
        if(isset($fileextensiontomimetype[$fileextension])){
            $iconpath = $kernel->locateResource('@XxamCoreBundle/Resources/public/icons/'.(isset($this->thumbnailsizes[$size]) ? $this->thumbnailsizes[$size] : '64x64').'/mimetypes/'.$fileextensiontomimetype[$fileextension]);
            return file_get_contents($iconpath);
        }
        $iconpath = $kernel->locateResource('@XxamCoreBundle/Resources/public/icons/'.(isset($this->thumbnailsizes[$size]) ? $this->thumbnailsizes[$size] : '64x64').'/mimetypes/unknown.png');
        return file_get_contents($iconpath);
    }
    
    protected function getImageFromCache($cachename,$timestamp) {
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


    /**
     * Get List of Filesystem for root directory listing
     *
     * @param XxamFilesystem[] $filesystems
     * @return array
     */
    protected function getFilesystemsFolders(Array $filesystems){
        $children = Array();
        foreach ($filesystems as $filesystem) {
            $children[] = Array(
                'id' => (string)$filesystem->getId(),
                'basename' => (string)$filesystem->getId(),
                'name' => $filesystem->getFilesystemname(),
                'icon' => $filesystem->getUser() ? '/bundles/xxamcore/icons/16x16/folder_user.png' : '/bundles/xxamcore/icons/16x16/folder.png',
                'timestamp' => null,
                'type' => $filesystem->getUser() ? 'privatefs' : 'fs',
                'size' => null,
                'leaf' => false
            );
        }
        return $children;
    }

    /**
     * Get List of Folders for path
     *
     * @param XxamFilesystem $filesystem
     * @param $path
     * @return array
     */
    protected function getFoldersForPath(XxamFilesystem $filesystem, $path){
        $children = Array();
        $fs = $this->getFs($filesystem);
        $contents = $fs->listContents($path);
        //dump($contents);
        foreach ($contents as $content) {
            if ($content['type'] == 'dir') {
                $children[] = Array(
                    'id' => (string)$filesystem->getId() . '/' . $content['path'],
                    'basename' => $content['basename'],
                    'name' => $content['basename'],
                    'icon' => '/bundles/xxamcore/icons/16x16/folder.png',
                    'timestamp' => isset($content['timestamp']) ? $content['timestamp'] : null
                );
            }
        }
        return $children;
    }

    /*
     * Get List of Files/Folders for path
     */
    protected function getFilesForPath(XxamFilesystem $filesystem,$path){
        $children = Array();
        $fs = $this->getFs($filesystem);
        $contents = $fs->listContents($path);
        //dump($contents);
        foreach ($contents as $content) {
            $children[] = Array(
                'id' => (string)$filesystem->getId() . '/' . $content['path'],
                'name' => $content['basename'],
                'timestamp' => isset($content['timestamp']) ? $content['timestamp'] : null,
                'type' => $content['type'],
                'size' => isset($content['size']) ? $content['size'] : null,
                //'thumbnail'=>$fs->getThumbnail($content['path'],'s')
            );
        }
        return $children;
    }

    protected function getThumbnailResponse($path,Filesystem $fs,$size,$cachename){
        $fileextensionswiththumbnails = $this->container->getParameter('fileextensionswiththumbnails');
        $pathexpl = explode('.', $path);
        $fileextension = strtolower($pathexpl[count($pathexpl) - 1]);
        $ctime = new \DateTime();
        if ($size == 'xxs' || !in_array($fileextension, $fileextensionswiththumbnails)) {
            $imgdata=$this->getIcon($path, $size);

        } else {
            $timestamp = $fs->getTimestamp($path);
            $fromcache = $this->getImageFromCache($cachename, $timestamp);
            if ($fromcache) { //ist bereits im cache:
                $thumbnaildata = $fromcache;
                $ctime = new \DateTime();
                $ctime->setTimestamp(intval($thumbnaildata[0]));
                $imgdata=$thumbnaildata[1];
            } else {
                $thumbnaildata = $fs->getThumbnail($path, $size);
                if ($thumbnaildata[1]) {
                    $thumbnaildata[0] = $ctime->getTimestamp();
                    $this->memcached->set($cachename, serialize($ctime->getTimestamp() . '|' . $thumbnaildata[1]));
                    $imgdata=$thumbnaildata[1];

                } else { //icon ausgeben:
                    $imgdata=$this->getIcon($path, $size);
                }
            }
        }
        $expires = 1 * 24 * 60 * 60;
        $response = new Response($imgdata);
        $response->headers->set('Content-Length', strlen($imgdata));
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Last-Modified', $ctime->format('D, d M Y H:i:s') . ' GMT');
        $response->headers->set('Cache-Control', 'maxage=' . $expires);
        $response->headers->set('Content-Type', 'image/png; charset=UTF-8');
        $response->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
        return $response;
    }


    protected function throwJsonError($errormessage) {
        $response = new Response(json_encode(Array('success'=>'false','error' => $errormessage)));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }
    
    
    
}
