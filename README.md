#  HumusAmqpBundle #

![](https://github.com/genhoi/HumusAmqpBundle/workflows/CI/badge.svg?branch=master)

## Caution ##

Still in development and very unstable

## About ##

This bundle integrates the [HumusAmqp](https://github.com/prolic/HumusAmqp) library into Symfony.

## Installation ##

### For Symfony Framework >= 4.3 ###

Require the bundle and its dependencies with composer:

```bash
$ composer require genhoi/humus-amqp-bundle
```

Register the bundle:

```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = [
        new Humus\AmqpBundle\HumusAmqpBundle(),
    ];
}
```

### For a console application that uses Symfony Console, Dependency Injection and Config components ###

```
{
    "require": {
        "genhoi/humus-amqp-bundle": "^0.11.0",
    }
}
```

Register the extension:

```php
use Humus\AmqpBundle\DependencyInjection\HumusAmqpExtension;

// ...

$containerBuilder->registerExtension(new HumusAmqpExtension());
```

## Usage ##

Add the `humus` section in your configuration file:

```yaml
humus:
  amqp:
    driver: amqp-extension
    connection:
      default:
        vhost: "/"
        login: guest
        password: guest
        host: rabbitmq
        port: 5672
        connect_timeout: 10
        read_timeout: 10
        write_timeout: 10
    exchange:
      test_exchange:
        connection: default
        durable: true
        type: direct
      test_rpc_client:
        connection: default
        type: direct
      test_rpc_server:
        connection: default
        type: direct
    queue:
      test_queue:
        connection: default
        durable: true
        auto_setup_fabric: true
        auto_setup_exchanges: true
        arguments:
          x-dead-letter-exchange: test_exchange
          x-dead-letter-routing-key: delayed
        exchanges:
          test_exchange:
            routing_keys:
            - ''
            - key-1
      test_queue_delayed:
        connection: default
        durable: true
        auto_setup_fabric: true
        auto_setup_exchanges: true
        exchanges:
          test_exchange:
            routing_keys:
            - delayed
      test_rpc_client:
        connection: default
        exchanges:
          test_rpc_client: []
      test_rpc_server:
        connection: default
        name: test_rpc_server
        exchanges:
          test_rpc_server: []
    callback_consumer:
      test_queue_consumer:
        queue: test_queue
        delivery_callback: HumusTest\AmqpBundle\Functional\Callback\ConsumerDeliveryCallback
        error_callback: HumusTest\AmqpBundle\Functional\Callback\ConsumerErrorCallback
        logger: monolog
        qos:
          prefetch_count: 3
          prefetch_size: 0
    producer:
      test_producer:
        type: json
        exchange: test_exchange
    json_rpc_server:
      test_rpc_server:
        delivery_callback: HumusTest\AmqpBundle\Functional\Callback\RpcDeliveryCallback
        idle_timeout: 10
        queue: test_rpc_server
    json_rpc_client:
      test_rpc_client:
        queue: test_rpc_client
        exchanges:
        - test_rpc_server
```