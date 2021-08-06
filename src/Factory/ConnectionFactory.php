<?php

namespace Humus\AmqpBundle\Factory;

use Humus\Amqp\Connection;
use Humus\Amqp\Driver\Driver;
use Humus\Amqp\Driver\PhpAmqpLib\LazyConnection;
use Humus\Amqp\Driver\PhpAmqpLib\LazySocketConnection;
use Humus\Amqp\Driver\PhpAmqpLib\SocketConnection;
use Humus\Amqp\Driver\PhpAmqpLib\SslConnection;
use Humus\Amqp\Driver\PhpAmqpLib\StreamConnection;
use PhpAmqpLib\Connection\Heartbeat\PCNTLHeartbeatSender;

class ConnectionFactory
{
    public function create(string $driver, array $options): Connection
    {
        switch ($driver) {
            case Driver::AMQP_EXTENSION:
                $connection = new \Humus\Amqp\Driver\AmqpExtension\Connection($options);
                $connection->connect();
                break;
            case Driver::PHP_AMQP_LIB:
            default:
                if (!isset($options['type'])) {
                    throw new \InvalidArgumentException(
                        'For php-amqplib driver a connection type is required'
                    );
                }
                $type = $options['type'];
                unset($options['type']);

                $registerPCNTLHeartbeatSender = $options['register_pcntl_heartbeat_sender'] ?? false;
                unset($options['register_pcntl_heartbeat_sender']);

                switch ($type) {
                    case 'lazy':
                    case LazyConnection::class:
                        $connection = new LazyConnection($options);
                        break;
                    case 'lazy_socket':
                    case LazySocketConnection::class:
                        $connection = new LazySocketConnection($options);
                        break;
                    case 'socket':
                    case SocketConnection::class:
                        $connection = new SocketConnection($options);
                        break;
                    case 'ssl':
                    case SslConnection::class:
                        $connection = new SslConnection($options);
                        break;
                    case 'stream':
                    case StreamConnection::class:
                        $connection = new StreamConnection($options);
                        break;
                    default:
                        throw new \InvalidArgumentException(
                            'Invalid connection type for php-amqplib driver given'
                        );
                }

                if ($registerPCNTLHeartbeatSender) {
                    (new PCNTLHeartbeatSender($connection->getResource()))->register();
                }
                break;
        }

        return $connection;
    }
}
