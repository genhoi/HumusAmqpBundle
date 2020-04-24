<?php

namespace HumusTest\AmqpBundle\Functional\Tests\Command;

use Humus\AmqpBundle\Command\SetupFabricCommand;
use HumusTest\AmqpBundle\Functional\App;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class SetupFabricCommandTest extends TestCase
{
    public function test_setup_ok()
    {
        $command = App::get(SetupFabricCommand::class); /** @var SetupFabricCommand $command */
        $tester = new CommandTester($command);
        $tester->execute([]);

        $this->assertEquals(0, $tester->getStatusCode());
        $expectedOutput = <<<'OUT'
Exchange auto_setup_exchange declared
Exchange test-exchange declared
Exchange test_exchange declared
Exchange test_rpc_client declared
Exchange test_rpc_server declared
Queue test-queue declared
Queue test-queue bind to test-exchange with routing key ''
Queue test-queue bind to test-exchange with routing key 'key-1'
Queue test_queue declared
Queue test_queue bind to test_exchange with routing key ''
Queue test_queue bind to test_exchange with routing key 'key-1'
Queue test_queue_delayed declared
Queue test_queue_delayed bind to test_exchange with routing key 'delayed'
Queue test_rpc_client declared
Queue test_rpc_client bind to test_rpc_client with routing key ''
Queue test_rpc_server declared
Queue test_rpc_server bind to test_rpc_server with routing key ''
Done

OUT;
        $this->assertEquals($expectedOutput, $tester->getDisplay(true));

        App::getFabricService()->delete();
    }
}
