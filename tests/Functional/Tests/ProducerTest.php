<?php

namespace HumusTest\AmqpBundle\Functional\Tests;

use HumusTest\AmqpBundle\Functional\App;
use PHPUnit\Framework\TestCase;

class ProducerTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function test_publishMessage_ok()
    {
        $producer = App::getTestProducer();
        $fabricService = App::getFabricService();

        $fabricService->setup();
        $producer->publish(['message' => 'hello']);
        $fabricService->delete();
    }
}
