<?php

namespace HumusTest\AmqpBundle\Functional\Tests\Command;

use Humus\AmqpBundle\Command\DeleteFabricCommand;
use HumusTest\AmqpBundle\Functional\App;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class DeleteFabricCommandTest extends TestCase
{
    public function test_setup_ok()
    {
        App::getFabricService()->setup();

        $command = App::get(DeleteFabricCommand::class); /** @var DeleteFabricCommand $command */
        $tester = new CommandTester($command);
        $tester->execute([]);

        $this->assertEquals(0, $tester->getStatusCode());
        $expectedOutput = <<<OUT
Exchange test_exchange deleted
Queue test_queue deleted
Queue test_queue_delayed deleted
Done

OUT;
        $this->assertEquals($expectedOutput, $tester->getDisplay(true));
    }
}
