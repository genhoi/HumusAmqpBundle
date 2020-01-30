<?php

namespace HumusTest\AmqpBundle\Functional\Tests;

use Humus\Amqp\Consumer;
use Humus\Amqp\JsonProducer;
use Humus\Amqp\Producer;
use Humus\AmqpBundle\SetupFabric\FabricService;
use HumusTest\AmqpBundle\Functional\App;
use PHPUnit\Framework\TestCase;

class ConsumerTest extends TestCase
{

    public function test_consumeMessage_ok()
    {
        $producer = App::get('humus.amqp.producer.test_producer'); /** @var Producer $producer */
        $this->assertInstanceOf(JsonProducer::class, $producer);

        $consumer = App::get('humus.amqp.callback_consumer.test_queue_consumer'); /** @var Consumer $consumer */
        $this->assertInstanceOf(Consumer::class, $consumer);

        $setupFabric = App::get(FabricService::class); /** @var FabricService $setupFabric */
        $this->assertInstanceOf(FabricService::class, $setupFabric);

        $setupFabric->setup();

        $producer->publish(['message' => 'hello']);
        $consumer->consume(1);

        $setupFabric->delete();
    }
}
