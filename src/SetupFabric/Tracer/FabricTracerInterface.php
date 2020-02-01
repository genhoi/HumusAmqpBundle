<?php

namespace Humus\AmqpBundle\SetupFabric\Tracer;

interface FabricTracerInterface
{
    public function declaredQueue(string $queueName);

    public function declaredExchange(string $exchangeName);

    public function bindQueue(string $queueName, string $exchangeName, string $routingKey);

    public function bindExchange(string $exchangeName, string $boundExchangeName, string $routingKey);

    public function deleteQueue(string $queueName);

    public function deleteExchange(string $exchangeName);
}
