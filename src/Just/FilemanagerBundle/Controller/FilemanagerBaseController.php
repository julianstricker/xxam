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

class FilemanagerBaseController extends Controller {

    protected $thumbnailsizes=Array(
        "xxs"=>"32x32",
        "xs"=>"32x32",
        "s"=>"64x64",
        "m"=>"128x128",
    );
    
    protected function removeChildrenkeys($children){
        if (isset($children['children'])){
            $children['children']=array_values($children['children']);
            foreach($children['children'] as $key => $child){
                $children['children'][$key] = $this->removeChildrenkeys($child);
            }
        }
        return $children;
    }
    
    protected function getFilesystems(){
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
    
    protected function getFs($filesystem){
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
    
    protected function getIcon($path,$size){
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
    
    protected function throwJsonError($errormessage) {
        $response = new Response(json_encode(Array('success'=>'false','error' => $errormessage)));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }
    
    
    
}
