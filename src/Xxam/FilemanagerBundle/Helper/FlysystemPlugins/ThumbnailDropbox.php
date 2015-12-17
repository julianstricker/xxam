<?php
namespace Xxam\FilemanagerBundle\Helper\FlysystemPlugins;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\PluginInterface;

class ThumbnailDropbox implements PluginInterface
{
    /* @var FilesystemInterface $filesystem */
    protected $filesystem;

    /**
     * @param FilesystemInterface $filesystem
     */
    public function setFilesystem(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function getMethod()
    {
        return 'getThumbnail';
    }

    /**
     * @param null $path
     * @param string $size
     * @return mixed
     */
    public function handle($path = null, $size='s')
    {

        $adapter=$this->filesystem->getAdapter();
        if (get_class($adapter)=='League\Flysystem\Cached\CachedAdapter') $adapter=$adapter->getAdapter();
        $client=$adapter->getClient();
        $path=$adapter->applyPathPrefix($path);
        return $client->getThumbnail($path,'png',$size);
        
    }
}
