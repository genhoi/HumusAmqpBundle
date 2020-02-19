<?php

namespace HumusTest\AmqpBundle\Functional\Tests\Command;

use Humus\Amqp\JsonRpc\JsonRpcRequest;
use Humus\Amqp\JsonRpc\JsonRpcResponse;
use Humus\AmqpBundle\Command\JsonRpcServerCommand;
use HumusTest\AmqpBundle\Functional\App;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class JsonRpcServerCommandTest extends TestCase
{
    public function test_consume_ok()
    {
        App::getFabricService()->setup();

        $client = App::getJsonRpcClient();
        $client->addRequest(new JsonRpcRequest('test_rpc_server', 'range', [1, 3], 'request-3'));
        $client->addRequest(new JsonRpcRequest('test_rpc_server', 'range', [4, 6], 'request-4'));

        $command = App::get(JsonRpcServerCommand::class); /** @var JsonRpcServerCommand $command */
        $tester = new CommandTester($command);
        $tester->execute(['name' => 'test_rpc_server', '-a' => 2]);

        $responses = $client->getResponseCollection(5);
        $this->assertEquals(JsonRpcResponse::withResult('request-3', [1, 2, 3]), $responses->getResponse('request-3'));
        $this->assertEquals(JsonRpcResponse::withResult('request-4', [4, 5, 6]), $responses->getResponse('request-4'));

        $this->assertEquals(0, $tester->getStatusCode());
        $this->assertEquals('', $tester->getDisplay(true));

        App::getFabricService()->delete();
    }
}
