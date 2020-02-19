<?php

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
                    'connect_timeout' => 10,
                    'read_timeout' => 10,
                    'write_timeout' => 10,
                ],
            ],
        ]
    ]
];

