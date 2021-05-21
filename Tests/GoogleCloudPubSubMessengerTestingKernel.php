<?php

namespace CedricZiel\Symfony\Bundle\GoogleCloudPubSubMessenger\Tests;

use CedricZiel\Symfony\Bundle\GoogleCloudPubSubMessenger\GoogleCloudPubSubMessengerBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class GoogleCloudPubSubMessengerTestingKernel extends Kernel
{
    public function __construct(array $config)
    {
        parent::__construct('test', true);
    }

    public function registerBundles()
    {
        return [
            new GoogleCloudPubSubMessengerBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
    }

    public function getCacheDir()
    {
        return __DIR__.'/../var/cache/'.spl_object_hash($this);
    }
}
