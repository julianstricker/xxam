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
            new Just\AdminBundle\JustAdminBundle(),
            new Just\MailclientBundle\JustMailclientBundle(),
            new Just\CalendarBundle\JustCalendarBundle(),
            new Just\ContactBundle\JustContactBundle(),
            new Just\CodingBundle\JustCodingBundle(),
            new Just\FilemanagerBundle\JustFilemanagerBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new Just\UserBundle\JustUserBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Just\ThumbnailBundle\JustThumbnailBundle(),
            //new BIT\RatchetBundle\BITRatchetBundle(),
            new Voryx\RESTGeneratorBundle\VoryxRESTGeneratorBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle($this),
            new Nelmio\CorsBundle\NelmioCorsBundle(),
            //new Voryx\ThruwayBundle\VoryxThruwayBundle($this),
            //new Oneup\FlysystemBundle\OneupFlysystemBundle(),
            //new FOS\RestBundle\FOSRestBundle(),
            //new Exercise\HTMLPurifierBundle\ExerciseHTMLPurifierBundle(),
            new Just\CommBundle\JustCommBundle(),
            new Just\CmsAdminBundle\JustCmsAdminBundle(),

            new Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle(),
            new Doctrine\Bundle\PHPCRBundle\DoctrinePHPCRBundle(),
            new Symfony\Cmf\Bundle\RoutingBundle\CmfRoutingBundle(),
            new Symfony\Cmf\Bundle\RoutingAutoBundle\CmfRoutingAutoBundle(),
            new Symfony\Cmf\Bundle\CoreBundle\CmfCoreBundle(),
            new Symfony\Cmf\Bundle\MenuBundle\CmfMenuBundle(),
            new Symfony\Cmf\Bundle\ContentBundle\CmfContentBundle(),
            new Symfony\Cmf\Bundle\BlockBundle\CmfBlockBundle(),
            new Symfony\Cmf\Bundle\CreateBundle\CmfCreateBundle(),
            // Dependencies of the CmfMenuBundle
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            // Dependencies of the CmfBlockBundle
            new Sonata\CoreBundle\SonataCoreBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Lunetics\LocaleBundle\LuneticsLocaleBundle(),

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
