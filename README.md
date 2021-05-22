# Google Cloud Pub/Sub bundle

This bundle wires `cedricziel/messenger-pubsub` in a Symfony application.

It enables working Pub/Sub messages through the CLI and via push to an HTTP endpoint.

## Installation

```shell
composer require cedricziel/symfony-messenger-pubsub-bundle
```

## Configuration

Configure your Symfony Messenger by supplying a valid DSN using the `pubsub` scheme.

```
MESSENGER_TRANSPORT_DSN=pubsub://my-google-cloud-project/my-pubsub-topic?subscription=my-subscription
```

Activate the push routes:

```yaml
_pubsub_push:
  resource: '@GoogleCloudPubSubMessengerBundle/Resources/config/routes.xml'
  prefix: /
```

## License

MIT
