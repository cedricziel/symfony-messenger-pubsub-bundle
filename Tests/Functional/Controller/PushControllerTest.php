<?php

namespace CedricZiel\Symfony\Bundle\GoogleCloudPubSubMessenger\Tests\Controller;

use CedricZiel\Symfony\Bundle\GoogleCloudPubSubMessenger\Tests\App\AppKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;

class PushControllerTest extends TestCase
{
    public function testPushAction()
    {
        $client = new KernelBrowser(new AppKernel());
        $client->request(Request::METHOD_POST, '/_messenger/pubsub/my-subscription');

        self::assertSame($client->getResponse()->getStatusCode(), 204);
    }
}
