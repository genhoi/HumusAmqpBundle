<?php

namespace HumusTest\AmqpBundle\Functional\Tests\Command;

use Humus\AmqpBundle\Command\CallbackConsumerCommand;
use HumusTest\AmqpBundle\Functional\App;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CallbackConsumerCommandTest extends TestCase
{
    public function test_consume_ok()
    {
        App::getFabricService()->setup();
        App::getTestProducer()->publish(['message' => 'test consume command']);

        $command = App::get(CallbackConsumerCommand::class); /** @var CallbackConsumerCommand $command */
        $tester = new CommandTester($command);
        $tester->execute(['name' => 'test_queue_consumer', '-a' => 1]);

        $this->assertEquals(0, $tester->getStatusCode());
        $this->assertEquals('', $tester->getDisplay(true));

        App::getFabricService()->delete();
    }
}
