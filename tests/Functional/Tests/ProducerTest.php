<?php

namespace HumusTest\AmqpBundle\Functional\Tests;

use Humus\Amqp\JsonProducer;
use Humus\Amqp\Producer;
use Humus\AmqpBundle\SetupFabric\FabricService;
use HumusTest\AmqpBundle\Functional\App;
use PHPUnit\Framework\TestCase;

class ProducerTest extends TestCase
{
    public function test_publishMessage_ok()
    {
        $producer = App::get('humus.amqp.producer.test_producer'); /** @var Producer $producer */
        $this->assertInstanceOf(JsonProducer::class, $producer);

        $setupFabric = App::get(FabricService::class); /** @var FabricService $setupFabric */
        $this->assertInstanceOf(FabricService::class, $setupFabric);

        $setupFabric->setup();
        $producer->publish(['message' => 'hello']);
        $setupFabric->delete();
    }
}
