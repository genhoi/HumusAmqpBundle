# HumusAmqpBundle #

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
        "genhoi/humus-amqp-bundle": "^0.1.0",
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
      default-amqp-connection:
        host: localhost
        port: 5672
        login: guest
        password: guest
        vhost: "/"
        persistent: true
        read_timeout: 3
        write_timeout: 1
    exchange:
      demo:
        name: demo
        type: direct
        connection: default-amqp-connection
      demo.error:
        name: demo.error
        type: direct
        connection: default-amqp-connection
      topic-exchange:
        name: topic-exchange
        type: topic
        connection: default-amqp-connection
      demo-rpc-client:
        name: demo-rpc-client
        type: direct
        connection: default-amqp-connection
      demo-rpc-server:
        name: demo-rpc-server
        type: direct
        connection: default-amqp-connection
      demo-rpc-server2:
        name: demo-rpc-server2
        type: direct
        connection: default-amqp-connection
    queue:
      foo:
        name: foo
        exchanges:
          demo:
          - arguments:
              x-dead-letter-exchange: demo.error
        connection: default-amqp-connection
      demo-rpc-client:
        name: ''
        exchanges:
          demo-rpc-client: []
        connection: default-amqp-connection
      demo-rpc-server:
        name: demo-rpc-server
        exchanges:
          demo-rpc-server: []
        connection: default-amqp-connection
      demo-rpc-server2:
        name: demo-rpc-server2
        exchanges:
          demo-rpc-server2: []
        connection: default-amqp-connection
      info-queue:
        name: info-queue
        exchanges:
          topic-exchange:
          - routing_keys:
            - "#.err"
        connection: default-amqp-connection
    producer:
      demo-producer:
        type: plain
        exchange: demo
        qos:
          prefetch_size: 0
          prefetch_count: 10
        auto_setup_fabric: true
      topic-producer:
        exchange: topic-exchange
        auto_setup_fabric: true
    callback_consumer:
      demo-consumer:
        queue: foo
        callback: echo
        idle_timeout: 10
        delivery_callback: my_callback
      topic-consumer-error:
        queue: info-queue
        qos:
          prefetch_count: 100
        auto_setup_fabric: true
        callback: echo
        logger: consumer-logger
```