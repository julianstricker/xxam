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
            new Voryx\RESTGeneratorBundle\VoryxRESTGeneratorBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle($this),
            new Nelmio\CorsBundle\NelmioCorsBundle(),
            //new Oneup\FlysystemBundle\OneupFlysystemBundle(),
            //new FOS\RestBundle\FOSRestBundle(),
            //new Exercise\HTMLPurifierBundle\ExerciseHTMLPurifierBundle(),
            new Xxam\CommBundle\XxamCommBundle(),


        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }
}
