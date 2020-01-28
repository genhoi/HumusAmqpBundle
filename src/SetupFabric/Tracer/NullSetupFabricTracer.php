<?php

namespace Humus\AmqpBundle\SetupFabric\Tracer;

class NullSetupFabricTracer implements SetupFabricTracerInterface
{
    public function declaredQueue(string $queueName)
    {
    }

    public function declaredExchange(string $exchangeName)
    {
    }

    public function bindQueue(string $queueName, string $exchangeName, string $routingKey)
    {
    }

    public function bindExchange(string $exchangeName, string $boundExchangeName, string $routingKey)
    {
    }
}
