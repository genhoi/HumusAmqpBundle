<?php

namespace Humus\AmqpBundle;

use Humus\Amqp\Channel;
use Humus\Amqp\Connection;
use Humus\Amqp\Constants;
use Humus\Amqp\Exchange;
use Humus\AmqpBundle\SetupFabric\FabricService;

class SetupExchange implements Exchange
{
    protected Exchange $exchange;

    protected FabricService $setupFabricService;

    public function __construct(Exchange $exchange, FabricService $declareService)
    {
        $this->exchange = $exchange;
        $this->setupFabricService = $declareService;
    }

    /**
     * Setup exchange: declare exchange, bind to other exchange.
     */
    public function setup(): void
    {
        $this->setupFabricService->setupExchange($this->exchange);
    }

    public function getName(): string
    {
        return $this->exchange->getName();
    }

    public function setName(string $exchangeName): void
    {
        $this->exchange->setName($exchangeName);
    }

    public function getType(): string
    {
        return $this->exchange->getType();
    }

    public function setType(string $exchangeType): void
    {
        $this->exchange->setType($exchangeType);
    }

    public function getFlags(): int
    {
        return $this->exchange->getFlags();
    }

    public function setFlags(int $flags): void
    {
        $this->exchange->setFlags($flags);
    }

    public function getArgument(string $key)
    {
        return $this->exchange->getArgument($key);
    }

    public function getArguments(): array
    {
        return $this->exchange->getArguments();
    }

    public function setArgument(string $key, $value): void
    {
        $this->exchange->setArgument($key, $value);
    }

    public function setArguments(array $arguments): void
    {
        $this->exchange->setArguments($arguments);
    }

    public function declareExchange(): void
    {
        $this->exchange->declareExchange();
    }

    public function delete(string $exchangeName = '', int $flags = Constants::AMQP_NOPARAM): void
    {
        $this->exchange->delete($exchangeName, $flags);
    }

    public function bind(string $exchangeName, string $routingKey = '', array $arguments = []): void
    {
        $this->exchange->bind($exchangeName, $routingKey, $arguments);
    }

    public function unbind(string $exchangeName, string $routingKey = '', array $arguments = []): void
    {
        $this->exchange->unbind($exchangeName, $routingKey, $arguments);
    }

    public function publish(string $message, string $routingKey = '', int $flags = Constants::AMQP_NOPARAM, array $attributes = []): void
    {
        $this->exchange->publish($message, $routingKey, $flags, $attributes);
    }

    public function getChannel(): Channel
    {
        return $this->exchange->getChannel();
    }

    public function getConnection(): Connection
    {
        return $this->exchange->getConnection();
    }
}
