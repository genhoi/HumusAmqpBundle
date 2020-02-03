<?php

namespace Humus\AmqpBundle\SetupFabric\Tracer;

class NullFabricTracer implements FabricTracerInterface
{
    public function declaredQueue(string $queueName): void
    {
    }

    public function declaredExchange(string $exchangeName): void
    {
    }

    public function bindQueue(string $queueName, string $exchangeName, string $routingKey): void
    {
    }

    public function bindExchange(string $exchangeName, string $boundExchangeName, string $routingKey): void
    {
    }

    public function deleteQueue(string $queueName): void
    {
    }

    public function deleteExchange(string $exchangeName): void
    {
    }
}
