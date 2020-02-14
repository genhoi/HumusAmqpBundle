<?php

namespace HumusTest\AmqpBundle\Functional\Callback;

use Humus\Amqp\DeliveryResult;
use Humus\Amqp\Envelope;
use Humus\Amqp\Queue;

class ConsumerDeliveryCallback
{
    public function __invoke(Envelope $envelope, Queue $queue)
    {
        $body = $envelope->getBody();

        return DeliveryResult::MSG_ACK();
    }
}
