<?php

namespace HumusTest\AmqpBundle\Functional;

use Humus\Amqp\Consumer;
use Humus\Amqp\Exchange;
use Humus\Amqp\JsonRpc\Client;
use Humus\Amqp\JsonRpc\JsonRpcServer;
use Humus\Amqp\Producer;
use Humus\Amqp\Queue;
use Humus\AmqpBundle\DependencyInjection\HumusAmqpExtension;
use Humus\AmqpBundle\SetupFabric\FabricService;
use HumusTest\AmqpBundle\Functional\Callback\ConsumerDeliveryCallback;
use HumusTest\AmqpBundle\Functional\Callback\ConsumerErrorCallback;
use HumusTest\AmqpBundle\Functional\Callback\RpcDeliveryCallback;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class App
{
    /**
     * @var self
     */
    private static $instance;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * App constructor.
     */
    public function __construct()
    {
        $driverEnvName = 'HUMUS_AMQP_BUNDLE_TEST_DRIVER';
        $driverName = $_ENV[$driverEnvName] ?? getenv($driverEnvName) ?? 'amqp-extension';

        $driverConfig = require __DIR__."/config/driver-$driverName.php";
        $humusConfig = require __DIR__.'/config/humus_amqp.php';

        $humusConfig = array_merge_recursive($driverConfig, $humusConfig);

        $this->container = new ContainerBuilder();
        $this->loadConsumerCallback($this->container);
        $this->container->registerExtension(new HumusAmqpExtension());
        $this->container->loadFromExtension('humus', $humusConfig['humus']);

        // Set public all services
        $this->container->addCompilerPass(new class() implements CompilerPassInterface {
            public function process(ContainerBuilder $container)
            {
                $definitions = $container->getDefinitions();
                foreach ($definitions as $definition) {
                    $definition->setPublic(true);
                }
            }
        });

        $this->container->compile();
    }

    protected function loadConsumerCallback(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            ConsumerDeliveryCallback::class => new Definition(ConsumerDeliveryCallback::class),
            ConsumerErrorCallback::class    => new Definition(ConsumerErrorCallback::class),
            RpcDeliveryCallback::class      => new Definition(RpcDeliveryCallback::class),
        ]);
    }

    public static function get(string $id)
    {
        if (self::$instance) {
            return self::$instance->container->get($id);
        }
        self::$instance = new self();

        return self::$instance->container->get($id);
    }

    public static function getFabricService(): FabricService
    {
        return self::get(FabricService::class);
    }

    public static function getTestQueue(): Queue
    {
        return self::get('humus.amqp.queue.test_queue');
    }

    public static function getTestExchange(): Exchange
    {
        return self::get('humus.amqp.exchange.test_exchange');
    }

    public static function getTestQueueConsumer(): Consumer
    {
        return self::get('humus.amqp.callback_consumer.test_queue_consumer');
    }

    public static function getTestProducer(): Producer
    {
        return self::get('humus.amqp.producer.test_producer');
    }

    public static function getJsonRpcClient(): Client
    {
        return self::get('humus.amqp.json_rpc_client.test_rpc_client');
    }

    public static function getJsonRpcServer(): JsonRpcServer
    {
        return self::get('humus.amqp.json_rpc_server.test_rpc_server');
    }
}
