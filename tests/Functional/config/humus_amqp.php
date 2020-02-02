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
                    'auto_setup_fabric' => true,
                    'auto_setup_exchanges' => true,
                    'arguments' => [
                        'x-dead-letter-exchange' => 'test_exchange',
                        'x-dead-letter-routing-key' => 'delayed',
                    ],
                    'exchanges' => [
                        'test_exchange' => [
                            'routing_keys' => ['', 'key-1']
                        ],
                    ],
                ],
                'test_queue_delayed' => [
                    'connection' => 'default',
                    'durable' => true,
                    'auto_setup_fabric' => true,
                    'auto_setup_exchanges' => true,
                    'exchanges' => [
                        'test_exchange' => [
                            'routing_keys' => ['delayed']
                        ],
                    ],
                ],
            ],
            'callback_consumer' => [
                'test_queue_consumer' => [
                    'queue' => 'test_queue',
                    'delivery_callback' => DeliveryCallback::class,
                    'error_callback' => ErrorCallback::class,
                    'qos' => [
                        'prefetch_count' => 3,
                        'prefetch_size' => 0,
                    ]
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