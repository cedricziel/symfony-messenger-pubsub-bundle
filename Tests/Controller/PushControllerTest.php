<?php

namespace CedricZiel\Symfony\Bundle\GoogleCloudPubSubMessenger\Tests\Controller;

use CedricZiel\Symfony\Bundle\GoogleCloudPubSubMessenger\Tests\GoogleCloudPubSubMessengerTestingKernel;
use PHPUnit\Framework\TestCase;

class PushControllerTest extends TestCase
{
    public function testPushAction()
    {
        $kernel = new GoogleCloudPubSubMessengerTestingKernel([
            'word_provider' => 'stub_word_list'
        ]);
        $kernel->boot();
        $container = $kernel->getContainer();
    }
}
