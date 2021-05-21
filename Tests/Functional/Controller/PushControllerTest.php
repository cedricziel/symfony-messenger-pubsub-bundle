<?php

namespace CedricZiel\Symfony\Bundle\GoogleCloudPubSubMessenger\Tests\Controller;

use CedricZiel\Symfony\Bundle\GoogleCloudPubSubMessenger\Tests\App\AppKernel;
use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Tests\Fixtures\DummyMessage;
use Google\Cloud\PubSub\Message;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;

class PushControllerTest extends WebTestCase
{
    protected static $class = AppKernel::class;

    public function testWillThrowIfNoSuchTransport()
    {
        $client = self::createClient();
        $client->request(Request::METHOD_POST, '/_messenger/pubsub/nonexistent-transport');

        self::assertSame(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }

    public function testWillThrowIfTransportNotPubSub()
    {
        $client = self::createClient();
        $client->request(Request::METHOD_POST, '/_messenger/pubsub/sync');

        self::assertSame(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
    }

    public function testPushActionWithoutPayloadWillThrow()
    {
        $client = self::createClient();
        $client->request(Request::METHOD_POST, '/_messenger/pubsub/my-pubsub-transport');

        self::assertSame(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
    }

    /**
     * @dataProvider getMessages
     */
    public function testPushAction($message)
    {
        $client = self::createClient();
        $server = ['HTTP_CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'];
        $client->request(Request::METHOD_POST, '/_messenger/pubsub/my-pubsub-transport', [], [], $server, $message);

        self::assertSame(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
    }

    public function getMessages()
    {
        $serializer = new PhpSerializer();
        $envelope = new Envelope(new DummyMessage('Yo'));

        return [
            ['{
                "message": {
                    "attributes": {
                        "key": "value"
                    },
                    "data": "'. base64_encode($serializer->encode($envelope)['body']) .'",
                    "messageId": "2070443601311540",
                    "message_id": "2070443601311540",
                    "publishTime": "2021-02-26T19:13:55.749Z",
                    "publish_time": "2021-02-26T19:13:55.749Z"
                },
               "subscription": "projects/myproject/subscriptions/mysubscription"
            }']
        ];
    }
}
