<?php

namespace HumusTest\AmqpBundle\Functional\Tests\Command;

use Humus\AmqpBundle\Command\RedeclareQueueCommand;
use HumusTest\AmqpBundle\Functional\App;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class RedeclareQueueCommandTest extends TestCase
{
    public function test_redeclareQueue_ok()
    {
        App::getFabricService()->setup();

        App::getTestProducer()->publish(['message' => 'test redeclare queue command']);
        App::getTestProducer()->publish(['message' => 'test redeclare queue command']);
        App::getTestProducer()->publish(['message' => 'test redeclare queue command']);

        $command = App::get(RedeclareQueueCommand::class); /** @var RedeclareQueueCommand $command */
        $tester = new CommandTester($command);
        $tester->execute(['queue' => 'test_queue']);

        $this->assertEquals(0, $tester->getStatusCode());

        App::getFabricService()->delete();
    }
}
