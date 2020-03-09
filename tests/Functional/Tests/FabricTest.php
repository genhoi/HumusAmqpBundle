<?php

namespace HumusTest\AmqpBundle\Functional\Tests;

use HumusTest\AmqpBundle\Functional\App;
use PHPUnit\Framework\TestCase;

class FabricTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function test_delete_ok()
    {
        $fabricService = App::getFabricService();
        $fabricService->delete();
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_setup_ok()
    {
        $fabricService = App::getFabricService();
        $fabricService->setup();
        $fabricService->delete();
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_setupQueue_ok()
    {
        $fabricService = App::getFabricService();
        $testQueue = App::getTestQueue();

        $fabricService->setupQueue($testQueue, true);
        $fabricService->delete();
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_setupExchange_ok()
    {
        $fabricService = App::getFabricService();
        $testExchange = App::getTestExchange();

        $fabricService->setupExchange($testExchange);
        $fabricService->delete();
    }
}
