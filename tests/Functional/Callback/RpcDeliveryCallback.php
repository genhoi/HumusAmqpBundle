<?php

namespace HumusTest\AmqpBundle\Functional\Callback;

use Humus\Amqp\JsonRpc\JsonRpcResponse;
use Humus\Amqp\JsonRpc\Request;

class RpcDeliveryCallback
{
    public function __invoke(Request $request)
    {
        $method = $request->method();
        $result = $method(...$request->params());

        return JsonRpcResponse::withResult($request->id(), $result);
    }
}
