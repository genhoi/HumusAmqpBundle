<?php

use HumusTest\AmqpBundle\Functional\ConsumerCallback\DeliveryCallback;
use HumusTest\AmqpBundle\Functional\ConsumerCallback\ErrorCallback;

return [
    'humus' => [
        'amqp' => [
            'driver' => 'amqp-extension',
            'connection' => [
                'default' => [
                    'vhost' => '/',
                    'login' => 'guest',
                    'password' => 'guest',
                    'host' => 'rabbitmq',
                    'port' => 5672,
                ],
            ],
            'exchange' => [
                'test_exchange' => [
                    'connection' => 'default',
                    'durable' => true,
                    'type' => 'direct',
                ],
            ],
            'queue' => [
                'test_queue' => [
                    'connection' => 'default',
                    'durable' => true,
                    'exchanges' => [
                        'test_exchange' => [
                            'routing_keys' => ['']
                        ],
                    ],
                ],
            ],
            'callback_consumer' => [
                'test_queue_consumer' => [
                    'queue' => 'test_queue',
                    'delivery_callback' => DeliveryCallback::class,
                    'error_callback' => ErrorCallback::class,
                ],
            ],
            'producer' => [
                'test_producer' => [
                    'type' => 'json',
                    'exchange' => 'test_exchange'
                ],
            ],
        ],
    ],
];