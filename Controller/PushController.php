<?php

namespace CedricZiel\Symfony\Bundle\GoogleCloudPubSubMessenger\Controller;

use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\PubSubReceivedStamp;
use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\PubSubReceiver;
use Google\Cloud\PubSub\PubSubClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Exception\RejectRedeliveredMessageException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;
use function get_class;

/**
 * @final
 */
class PushController
{
    /**
     * @var MessageBusInterface
     */
    private $bus;

    /**
     * @var EventDispatcherInterface|null
     */
    private $eventDispatcher;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @var PubSubReceiver
     */
    private $pubSubReceiver;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(MessageBusInterface $bus, PubSubReceiver $pubSubReceiver, SerializerInterface $serializer, EventDispatcherInterface $eventDispatcher = null, LoggerInterface $logger = null)
    {
        $this->bus = $bus;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
        $this->pubSubReceiver = $pubSubReceiver;
        $this->serializer = $serializer;
    }

    public function __invoke(Request $request)
    {
        $pubSub = new PubSubClient();

        $rawMessage = json_decode($request->getContent(), true);
        $message = $pubSub->consume($rawMessage);

        $body = $message->data();
        $attributes = $message->attributes();

        try {
            $envelope = $this->serializer->decode([
                'body' => $body,
                'headers' => $attributes,
            ]);
        } catch (MessageDecodingFailedException $exception) {

            throw $exception;
        }

        $envelope = $envelope->with(new PubSubReceivedStamp($message, $message->subscription()));

        $this->handleMessage($envelope, $this->pubSubReceiver, 'pubsub');
    }

    private function handleMessage(Envelope $envelope, ReceiverInterface $receiver, string $transportName): void
    {
        $event = new WorkerMessageReceivedEvent($envelope, $transportName);
        $this->dispatchEvent($event);
        $envelope = $event->getEnvelope();

        if (!$event->shouldHandle()) {
            return;
        }

        try {
            $envelope = $this->bus->dispatch($envelope->with(new ReceivedStamp($transportName), new ConsumedByWorkerStamp()));
        } catch (Throwable $throwable) {
            $rejectFirst = $throwable instanceof RejectRedeliveredMessageException;
            if ($rejectFirst) {
                // redelivered messages are rejected first so that continuous failures in an event listener or while
                // publishing for retry does not cause infinite redelivery loops
                $receiver->reject($envelope);
            }

            if ($throwable instanceof HandlerFailedException) {
                $envelope = $throwable->getEnvelope();
            }

            $failedEvent = new WorkerMessageFailedEvent($envelope, $transportName, $throwable);
            $this->dispatchEvent($failedEvent);
            $envelope = $failedEvent->getEnvelope();

            if (!$rejectFirst) {
                $receiver->reject($envelope);
            }

            return;
        }

        $handledEvent = new WorkerMessageHandledEvent($envelope, $transportName);
        $this->dispatchEvent($handledEvent);
        $envelope = $handledEvent->getEnvelope();

        if (null !== $this->logger) {
            $message = $envelope->getMessage();
            $context = [
                'message' => $message,
                'class' => get_class($message),
            ];
            $this->logger->info('{class} was handled successfully (acknowledging to transport).', $context);
        }

        $receiver->ack($envelope);
    }

    private function dispatchEvent(object $event): void
    {
        if (null === $this->eventDispatcher) {
            return;
        }

        $this->eventDispatcher->dispatch($event);
    }
}
