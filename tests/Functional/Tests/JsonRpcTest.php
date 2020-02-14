<?php

namespace HumusTest\AmqpBundle\Functional\Tests;

use Humus\Amqp\JsonRpc\JsonRpcRequest;
use Humus\Amqp\JsonRpc\JsonRpcResponse;
use HumusTest\AmqpBundle\Functional\App;
use PHPUnit\Framework\TestCase;

class JsonRpcTest extends TestCase
{
    public function test_jsonRpc_ok()
    {
        App::getFabricService()->setup();

        $client = App::getJsonRpcClient();
        $client->addRequest(
            new JsonRpcRequest('test_rpc_server', 'range', [1, 3], 'request-1')
        );
        $client->addRequest(
            new JsonRpcRequest('test_rpc_server', 'json_encode', [['message' => 'hello']], 'request-2')
        );

        $server = App::getJsonRpcServer();
        $server->consume(2);

        $responses = $client->getResponseCollection(5);
        $this->assertEquals(
            JsonRpcResponse::withResult('request-1', [1, 2, 3]),
            $responses->getResponse('request-1')
        );
        $this->assertEquals(
            JsonRpcResponse::withResult('request-2', '{"message":"hello"}'),
            $responses->getResponse('request-2')
        );

        App::getFabricService()->delete();
    }
}
