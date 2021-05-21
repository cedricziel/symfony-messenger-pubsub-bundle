# Google Cloud Pub/Sub bundle

This bundle wires `cedricziel/messenger-pubsub` in a Symfony application.

It also enables Pub/Sub message working for Push subscriptions.

## Installation

```shell
composer require cedricziel/symfony-messenger-pubsub-bundle
```

## Configuration

Activate the push routes:

```yaml
_pubsub_push:
  resource: '@GoogleCloudPubSubMessengerBundle/Resources/config/routes.xml'
  prefix: /
```

## License

MIT
