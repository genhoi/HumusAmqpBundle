<?php

return [
    'humus' => [
        'amqp' => [
            'driver'     => 'php-amqplib',
            'connection' => [
                'default' => [
                    'type'            => 'stream',
                    'vhost'           => '/',
                    'login'           => 'guest',
                    'password'        => 'guest',
                    'host'            => 'rabbitmq',
                    'port'            => 5672,
                    'connect_timeout' => 10,
                    'read_timeout'    => 10,
                    'write_timeout'   => 10,
                    'heartbeat'       => 4,
                    'register_pcntl_heartbeat_sender' => true,
                ],
            ],
        ],
    ],
];
