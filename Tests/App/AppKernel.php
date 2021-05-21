<?php

namespace CedricZiel\Symfony\Bundle\GoogleCloudPubSubMessenger\Tests\App;

use CedricZiel\Symfony\Bundle\GoogleCloudPubSubMessenger\GoogleCloudPubSubMessengerBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

final class AppKernel extends Kernel
{
    use MicroKernelTrait {
        MicroKernelTrait::registerContainerConfiguration as parentRegisterContainerConfiguration;
    }

    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new GoogleCloudPubSubMessengerBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $this->parentRegisterContainerConfiguration($loader);

        $loader->load($this->getProjectDir().'/config/services.yaml');
    }

    public function getCacheDir(): string
    {
        return $this->getBaseDir() . 'cache';
    }

    private function getBaseDir(): string
    {
        return sys_get_temp_dir() . '/pubsub-messenger-bundle/' . spl_object_id($this) . '/var/';
    }

    public function getLogDir(): string
    {
        return $this->getBaseDir() . 'log';
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $routes->import($this->getProjectDir() . '/config/routes.yaml');
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    protected function configureContainer(ContainerBuilder $containerBuilder, LoaderInterface $loader): void
    {
        $loader->load($this->getProjectDir() . '/config/config.yaml');
    }
}
