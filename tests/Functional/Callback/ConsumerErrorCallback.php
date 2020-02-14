<?php

namespace HumusTest\AmqpBundle\Functional\Callback;

use Humus\Amqp\AbstractConsumer;

class ConsumerErrorCallback
{
    public function __invoke(\Throwable $throwable, AbstractConsumer $consumer)
    {
        return false;
    }
}
