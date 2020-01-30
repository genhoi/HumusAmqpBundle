<?php

namespace HumusTest\AmqpBundle\Functional\ConsumerCallback;

use Humus\Amqp\DeliveryResult;
use Humus\Amqp\Envelope;
use Humus\Amqp\Queue;

class DeliveryCallback
{
    public function __invoke(Envelope $envelope, Queue $queue)
    {
        $body = $envelope->getBody();

        return DeliveryResult::MSG_ACK();
    }
}
