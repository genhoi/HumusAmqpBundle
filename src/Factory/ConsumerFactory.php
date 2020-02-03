<?php

namespace Humus\AmqpBundle\Factory;

use Humus\Amqp\CallbackConsumer;
use Humus\Amqp\Queue;
use Psr\Log\LoggerInterface;

class ConsumerFactory
{
    public function create(
        Queue $queue,
        LoggerInterface $logger,
        callable $deliveryCallback,
        ?callable $flushCallback,
        ?callable $errorCallback,
        array $options = []
    ): CallbackConsumer
    {
        $qosPrefetchSize = $options['qos']['prefetch_size'] ?? 0;
        $qosPrefetchCount = $options['qos']['prefetch_count'] ?? 3;
        $idleTimeout = $options['idle_timeout'] ?? 5.0;
        $consumerTag = $options['consumer_tag'] ?? '';

        $channel = $queue->getChannel();
        $channel->qos($qosPrefetchSize, $qosPrefetchCount);

        return new CallbackConsumer($queue, $logger, $idleTimeout, $deliveryCallback, $flushCallback, $errorCallback, $consumerTag);
    }
}
