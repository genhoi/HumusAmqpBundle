<?php

namespace Humus\AmqpBundle\Factory;

use Humus\Amqp\Channel;
use Humus\Amqp\Constants;
use Humus\Amqp\Exchange;
use Humus\AmqpBundle\SetupExchange;
use Humus\AmqpBundle\SetupFabric\FabricService;

class ExchangeFactory
{
    protected FabricService $fabricService;

    public function __construct(FabricService $fabricService)
    {
        $this->fabricService = $fabricService;
    }

    public function createSetup(Channel $channel, array $options): SetupExchange
    {
        $exchange = $this->create($channel, $options);

        return new SetupExchange($exchange, $this->fabricService);
    }

    public function create(Channel $channel, array $options): Exchange
    {
        $exchange = $channel->newExchange();

        $exchange->setArguments($options['arguments']);
        $exchange->setName($options['name']);
        $exchange->setFlags($this->getFlags($options));
        $exchange->setType($options['type']);

        return $exchange;
    }

    private function getFlags(array $options): int
    {
        $flags = 0;
        $flags |= $options['passive'] ? Constants::AMQP_PASSIVE : 0;
        $flags |= $options['durable'] ? Constants::AMQP_DURABLE : 0;
        $flags |= $options['auto_delete'] ? Constants::AMQP_AUTODELETE : 0; // RabbitMQ Extension
        $flags |= $options['internal'] ? Constants::AMQP_INTERNAL : 0; // RabbitMQ Extension

        return $flags;
    }
}
