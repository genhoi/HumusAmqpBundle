<?php

namespace Humus\AmqpBundle;

use Humus\Amqp\Channel;
use Humus\Amqp\Connection;
use Humus\Amqp\Constants;
use Humus\Amqp\Queue;
use Humus\AmqpBundle\SetupFabric\FabricService;

class SetupQueue implements Queue
{
    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var FabricService
     */
    protected $setupFabricService;

    /**
     * @param Queue         $queue
     * @param FabricService $declareService
     */
    public function __construct(Queue $queue, FabricService $declareService)
    {
        $this->queue = $queue;
        $this->setupFabricService = $declareService;
    }

    /**
     * Setup queue: declare, bind to exchange.
     *
     * @param bool $setupExchanges Declare related exchanges
     */
    public function setup(bool $setupExchanges): void
    {
        $this->setupFabricService->setupQueue($this->queue, $setupExchanges);
    }

    public function getName(): string
    {
        return $this->queue->getName();
    }

    public function setName(string $queueName)
    {
        $this->queue->setName($queueName);
    }

    public function getFlags(): int
    {
        return $this->queue->getFlags();
    }

    public function setFlags(int $flags)
    {
        $this->queue->setFlags($flags);
    }

    public function getArgument(string $key)
    {
        return $this->queue->getArgument($key);
    }

    public function getArguments(): array
    {
        return $this->queue->getArguments();
    }

    public function setArgument(string $key, $value)
    {
        $this->queue->setArgument($key, $value);
    }

    public function setArguments(array $arguments)
    {
        $this->queue->setArguments($arguments);
    }

    public function declareQueue(): int
    {
        return $this->queue->declareQueue();
    }

    public function bind(string $exchangeName, string $routingKey = '', array $arguments = [])
    {
        $this->queue->bind($exchangeName, $routingKey, $arguments);
    }

    public function get(int $flags = Constants::AMQP_NOPARAM)
    {
        return $this->queue->get($flags);
    }

    public function consume(callable $callback = null, int $flags = Constants::AMQP_NOPARAM, string $consumerTag = '')
    {
        $this->queue->consume($callback, $flags, $consumerTag);
    }

    public function ack(int $deliveryTag, int $flags = Constants::AMQP_NOPARAM)
    {
        $this->queue->ack($deliveryTag, $flags);
    }

    public function nack(int $deliveryTag, int $flags = Constants::AMQP_NOPARAM)
    {
        $this->queue->nack($deliveryTag, $flags);
    }

    public function reject(int $deliveryTag, int $flags = Constants::AMQP_NOPARAM)
    {
        $this->queue->reject($deliveryTag, $flags);
    }

    public function purge()
    {
        $this->queue->purge();
    }

    public function cancel(string $consumerTag = '')
    {
        $this->queue->cancel($consumerTag);
    }

    public function unbind(string $exchangeName, string $routingKey = '', array $arguments = [])
    {
        $this->queue->unbind($exchangeName, $routingKey, $arguments);
    }

    public function delete(int $flags = Constants::AMQP_NOPARAM)
    {
        $this->queue->delete($flags);
    }

    public function getChannel(): Channel
    {
        return $this->queue->getChannel();
    }

    public function getConnection(): Connection
    {
        return $this->queue->getConnection();
    }
}
