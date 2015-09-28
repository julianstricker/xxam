<?php
namespace Just\FilemanagerBundle\Helper\FlysystemPlugins;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\PluginInterface;

class ThumbnailDropbox implements PluginInterface
{
    protected $filesystem;

    public function setFilesystem(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function getMethod()
    {
        return 'getThumbnail';
    }

    public function handle($path = null,$size='s')
    {
        $contents = $this->filesystem->read($path);
        $adapter=$this->filesystem->getAdapter();
        if (get_class($adapter)=='League\Flysystem\Cached\CachedAdapter') $adapter=$adapter->getAdapter();
        $client=$adapter->getClient();
        $path=$adapter->applyPathPrefix($path);
        return $client->getThumbnail($path,'png',$size);
        
    }
}
