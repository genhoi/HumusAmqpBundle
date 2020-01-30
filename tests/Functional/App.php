<?php

namespace HumusTest\AmqpBundle\Functional;

use Humus\AmqpBundle\DependencyInjection\HumusAmqpExtension;
use HumusTest\AmqpBundle\Functional\ConsumerCallback\DeliveryCallback;
use HumusTest\AmqpBundle\Functional\ConsumerCallback\ErrorCallback;
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
        $humusConfig = require __DIR__ . '/config/humus_amqp.php';

        $this->container = new ContainerBuilder();
        $this->loadConsumerCallback($this->container);
        $this->container->registerExtension(new HumusAmqpExtension());
        $this->container->loadFromExtension('humus', $humusConfig['humus']);

        // Set public all services
        $this->container->addCompilerPass(new class implements CompilerPassInterface {
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

    public static function get(string $id)
    {
        if (self::$instance) {
            return self::$instance->container->get($id);
        }
        self::$instance = new self();

        return self::$instance->container->get($id);
    }

    protected function loadConsumerCallback(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            DeliveryCallback::class => new Definition(DeliveryCallback::class),
            ErrorCallback::class => new Definition(ErrorCallback::class),
        ]);
    }

}
