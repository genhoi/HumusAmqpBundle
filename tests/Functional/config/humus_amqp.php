<?php

use HumusTest\AmqpBundle\Functional\Callback\ConsumerDeliveryCallback;
use HumusTest\AmqpBundle\Functional\Callback\ConsumerErrorCallback;
use HumusTest\AmqpBundle\Functional\Callback\RpcDeliveryCallback;

return [
    'humus' => [
        'amqp' => [
            'exchange' => [
                'test_exchange' => [
                    'connection' => 'default',
                    'durable'    => true,
                    'type'       => 'direct',
                ],
                'test-exchange' => [
                    'connection' => 'default',
                    'durable'    => true,
                    'type'       => 'direct',
                ],
                'test_rpc_client' => [
                    'connection' => 'default',
                    'type'       => 'direct',
                ],
                'test_rpc_server' => [
                    'connection' => 'default',
                    'type'       => 'direct',
                ],
                'auto_setup_exchange' => [
                    'connection'        => 'default',
                    'type'              => 'direct',
                    'durable'           => true,
                    'auto_setup_fabric' => true,
                ],
            ],
            'queue' => [
                'test_queue' => [
                    'connection'           => 'default',
                    'durable'              => true,
                    'auto_setup_fabric'    => true,
                    'auto_setup_exchanges' => true,
                    'arguments'            => [
                        'x-dead-letter-exchange'    => 'test_exchange',
                        'x-dead-letter-routing-key' => 'delayed',
                    ],
                    'exchanges' => [
                        'test_exchange' => [
                            'routing_keys' => ['', 'key-1'],
                        ],
                    ],
                ],
                'test-queue' => [
                    'connection'           => 'default',
                    'durable'              => true,
                    'auto_setup_fabric'    => true,
                    'auto_setup_exchanges' => true,
                    'arguments'            => [
                        'x-dead-letter-exchange'    => 'test-exchange',
                        'x-dead-letter-routing-key' => 'delayed',
                    ],
                    'exchanges' => [
                        'test-exchange' => [
                            'routing_keys' => ['', 'key-1'],
                        ],
                    ],
                ],
                'test_queue_delayed' => [
                    'connection'           => 'default',
                    'durable'              => true,
                    'auto_setup_fabric'    => true,
                    'auto_setup_exchanges' => true,
                    'exchanges'            => [
                        'test_exchange' => [
                            'routing_keys' => ['delayed'],
                        ],
                    ],
                ],
                'test_rpc_client' => [
                    'connection' => 'default',
                    'exchanges'  => [
                        'test_rpc_client' => [],
                    ],
                ],
                'test_rpc_server' => [
                    'connection' => 'default',
                    'name'       => 'test_rpc_server',
                    'exchanges'  => [
                        'test_rpc_server' => [],
                    ],
                ],
            ],
            'callback_consumer' => [
                'test_queue_consumer' => [
                    'queue'             => 'test_queue',
                    'delivery_callback' => ConsumerDeliveryCallback::class,
                    'error_callback'    => ConsumerErrorCallback::class,
                    'qos'               => [
                        'prefetch_count' => 3,
                        'prefetch_size'  => 0,
                    ],
                ],
            ],
            'producer' => [
                'test_producer' => [
                    'type'     => 'json',
                    'exchange' => 'test_exchange',
                ],
            ],
            'json_rpc_server' => [
                'test_rpc_server' => [
                    'delivery_callback' => RpcDeliveryCallback::class,
                    'idle_timeout'      => 10,
                    'queue'             => 'test_rpc_server',
                ],
            ],
            'json_rpc_client' => [
                'test_rpc_client' => [
                    'queue'     => 'test_rpc_client',
                    'exchanges' => [
                        'test_rpc_server',
                    ],
                ],
            ],
        ],
    ],
];
