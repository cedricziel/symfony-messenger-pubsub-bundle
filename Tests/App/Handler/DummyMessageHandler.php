<?php

namespace CedricZiel\Symfony\Bundle\GoogleCloudPubSubMessenger\Tests\App\Handler;

use CedricZiel\Symfony\Bundle\GoogleCloudPubSubMessenger\Tests\Fixtures\DummyMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DummyMessageHandler implements MessageHandlerInterface
{
    public function __invoke(DummyMessage $message)
    {
        // do the work
    }
}
