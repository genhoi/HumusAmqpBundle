<?php

namespace HumusTest\AmqpBundle\Functional\Tests\Command;

use Humus\AmqpBundle\Command\PublishMessageCommand;
use HumusTest\AmqpBundle\Functional\App;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class PublishMessageCommandTest extends TestCase
{
    public function test_publishMessage_ok()
    {
        App::getFabricService()->setup();

        $command = App::get(PublishMessageCommand::class); /** @var PublishMessageCommand $command */
        $tester = new CommandTester($command);
        $tester->execute([
            'producer'  => 'test_producer',
            '--message' => '{"message": "test publish command"}',
        ]);
        $this->assertEquals(0, $tester->getStatusCode());
        $this->assertEquals("Message published\n", $tester->getDisplay(true));

        App::getFabricService()->delete();
    }
}
