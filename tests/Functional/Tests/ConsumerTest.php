<?php

namespace HumusTest\AmqpBundle\Functional\Tests;

use HumusTest\AmqpBundle\Functional\App;
use PHPUnit\Framework\TestCase;

class ConsumerTest extends TestCase
{

    public function test_consumeMessage_ok()
    {
        $producer = App::getTestProducer();
        $consumer = App::getTestQueueConsumer();
        $fabricService = App::getFabricService();

        $fabricService->setup();

        $producer->publish(['message' => 'hello']);
        $consumer->consume(1);

        $fabricService->delete();
    }
}
