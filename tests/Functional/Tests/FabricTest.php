<?php

namespace HumusTest\AmqpBundle\Functional\Tests;

use Humus\AmqpBundle\SetupFabric\FabricService;
use HumusTest\AmqpBundle\Functional\App;
use PHPUnit\Framework\TestCase;

class FabricTest extends TestCase
{
    public function test_delete_ok()
    {
        $setupFabric = App::get(FabricService::class); /** @var FabricService $setupFabric */
        $this->assertInstanceOf(FabricService::class, $setupFabric);

        $setupFabric->delete();
    }

    public function test_setup_ok()
    {
        $setupFabric = App::get(FabricService::class); /** @var FabricService $setupFabric */
        $this->assertInstanceOf(FabricService::class, $setupFabric);

        $setupFabric->setup();
        $setupFabric->delete();
    }

    public function test_setupQueue_ok()
    {
        $setupFabric = App::get(FabricService::class); /** @var FabricService $setupFabric */
        $this->assertInstanceOf(FabricService::class, $setupFabric);

        $setupFabric->setupQueue('test_queue', true);
        $setupFabric->delete();
    }

    public function test_setupExchange_ok()
    {
        $setupFabric = App::get(FabricService::class); /** @var FabricService $setupFabric */
        $this->assertInstanceOf(FabricService::class, $setupFabric);

        $setupFabric->setupExchange('test_exchange');
        $setupFabric->delete();
    }
}
