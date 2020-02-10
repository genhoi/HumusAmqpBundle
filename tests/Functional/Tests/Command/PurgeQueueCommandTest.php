<?php

namespace HumusTest\AmqpBundle\Functional\Tests\Command;

use Humus\AmqpBundle\Command\PurgeQueueCommand;
use HumusTest\AmqpBundle\Functional\App;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class PurgeQueueCommandTest extends TestCase
{
    public function test_purgeQueues_ok()
    {
        App::getFabricService()->setup();

        App::getTestProducer()->publish(['message' => 'test purge command']);
        App::getTestProducer()->publish(['message' => 'test purge command for delayed'], 'delayed');

        $command = App::get(PurgeQueueCommand::class); /** @var PurgeQueueCommand $command */
        $tester = new CommandTester($command);
        $tester->execute(['name' => ['test_queue', 'test_queue_delayed']]);

        $this->assertEquals(0, $tester->getStatusCode());
        $this->assertEquals('', $tester->getDisplay(true));

        App::getFabricService()->delete();
    }
}
