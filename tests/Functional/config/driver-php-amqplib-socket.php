<?php

return [
    'humus' => [
        'amqp' => [
            'driver' => 'php-amqplib',
            'connection' => [
                'default' => [
                    'type' => 'socket',
                    'vhost' => '/',
                    'login' => 'guest',
                    'password' => 'guest',
                    'host' => 'rabbitmq',
                    'port' => 5672,
                    'connect_timeout' => 10,
                    'read_timeout' => 10,
                    'write_timeout' => 10,
                ],
            ],
        ]
    ]
];

