<?php
namespace Xxam\FilemanagerBundle\Helper\FlysystemPlugins;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\PluginInterface;
use Symfony\Component\HttpFoundation\Response;

class ThumbnailLocal implements PluginInterface
{

    private $thumbnailservice;

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

    protected $thumbnailsizes=Array(
        "xxs"=>"32",
        "xs"=>"32",
        "s"=>"64",
        "m"=>"128",
    );

    /**
     * @param null $path
     * @param string $size
     * @return mixed
     */
    public function handle($path = null, $size='s')
    {

        $adapter=$this->filesystem->getAdapter();
        if (get_class($adapter)=='League\Flysystem\Cached\CachedAdapter') $adapter=$adapter->getAdapter();

        //$path=$adapter->applyPathPrefix($path);

       // return $client->getThumbnail($path,'png',$size);
        /** @var Response $response */
        $response=$this->thumbnailservice->generateResponseForImage($path, $this->thumbnailsizes[$size], $this->thumbnailsizes[$size], 'normal', '');
        return $response->getContent();
        
    }

    public function __construct($thumbnailservice)
    {
        $this->thumbnailservice=$thumbnailservice;
    }
}
