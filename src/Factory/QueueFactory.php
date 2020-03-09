<?php

namespace Humus\AmqpBundle\Factory;

use Humus\Amqp\Channel;
use Humus\Amqp\Constants;
use Humus\Amqp\Queue;
use Humus\AmqpBundle\SetupFabric\FabricService;
use Humus\AmqpBundle\SetupQueue;

class QueueFactory
{
    /**
     * @var FabricService
     */
    protected $declareService;

    public function __construct(FabricService $declareService)
    {
        $this->declareService = $declareService;
    }

    public function createSetup(Channel $channel, array $options): SetupQueue
    {
        $queue = $this->create($channel, $options);

        return new SetupQueue($queue, $this->declareService);
    }

    public function create(Channel $channel, array $options): Queue
    {
        $queue = $channel->newQueue();

        $queue->setName($options['name'] ?? '');
        $queue->setFlags($this->getFlags($options));
        $queue->setArguments($options['arguments']);

        return $queue;
    }

    private function getFlags(array $options): int
    {
        $flags = 0;
        $flags |= $options['passive'] ? Constants::AMQP_PASSIVE : 0;
        $flags |= $options['durable'] ? Constants::AMQP_DURABLE : 0;
        $flags |= $options['exclusive'] ? Constants::AMQP_EXCLUSIVE : 0;
        $flags |= $options['auto_delete'] ? Constants::AMQP_AUTODELETE : 0;

        return $flags;
    }
}
