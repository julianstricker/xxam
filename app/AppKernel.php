<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Xxam\CoreBundle\XxamCoreBundle(),
            new Xxam\MailclientBundle\XxamMailclientBundle(),
            new Xxam\CalendarBundle\XxamCalendarBundle(),
            new Xxam\ContactBundle\XxamContactBundle(),
            new Xxam\CodingBundle\XxamCodingBundle(),
            new Xxam\FilemanagerBundle\XxamFilemanagerBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new Xxam\UserBundle\XxamUserBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Just\ThumbnailBundle\JustThumbnailBundle(),
            //new BIT\RatchetBundle\BITRatchetBundle(),
            //new Voryx\RESTGeneratorBundle\VoryxRESTGeneratorBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle($this),
            new Nelmio\CorsBundle\NelmioCorsBundle(),
            //new Oneup\FlysystemBundle\OneupFlysystemBundle(),
            //new FOS\RestBundle\FOSRestBundle(),
            //new Exercise\HTMLPurifierBundle\ExerciseHTMLPurifierBundle(),
            new Knp\Bundle\MarkdownBundle\KnpMarkdownBundle(), //fürs HelpBundle
            new Xxam\CommBundle\XxamCommBundle(),
            new Xxam\DynmodBundle\XxamDynmodBundle(),
            new Xxam\HelpBundle\XxamHelpBundle(),

        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }
    public function getCacheDir()
    {
        return dirname(__DIR__).'/var/cache/'.$this->getEnvironment();
    }
    public function getLogDir()
    {
        return dirname(__DIR__).'/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }

    public function __construct($environment, $debug)
    {
        // Two is better than one...
        //ini_set("date.timezone", "UTC");
        date_default_timezone_set('UTC');

        parent::__construct($environment, $debug);
    }
}
