<?php

namespace HumusTest\AmqpBundle\Functional\ConsumerCallback;

use Humus\Amqp\AbstractConsumer;

class ErrorCallback
{
    public function __invoke(\Throwable $throwable, AbstractConsumer $consumer)
    {
        return false;
    }
}
