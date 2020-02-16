<?php

namespace Humus\AmqpBundle\DependencyInjection;

use Humus\Amqp\CallbackConsumer;
use Humus\Amqp\Channel;
use Humus\Amqp\Driver\Driver;
use Humus\Amqp\Driver\PhpAmqpLib\LazyConnection;
use Humus\Amqp\Driver\PhpAmqpLib\LazySocketConnection;
use Humus\Amqp\Driver\PhpAmqpLib\SocketConnection;
use Humus\Amqp\Driver\PhpAmqpLib\SslConnection;
use Humus\Amqp\Driver\PhpAmqpLib\StreamConnection;
use Humus\Amqp\Exchange;
use Humus\Amqp\JsonProducer;
use Humus\Amqp\JsonRpc\JsonRpcClient;
use Humus\Amqp\JsonRpc\JsonRpcServer;
use Humus\Amqp\PlainProducer;
use Humus\Amqp\Queue;
use Humus\AmqpBundle\Binding\Binding;
use Humus\AmqpBundle\Binding\BindingRepository;
use Humus\AmqpBundle\Command\CallbackConsumerCommand;
use Humus\AmqpBundle\Command\DeleteFabricCommand;
use Humus\AmqpBundle\Command\JsonRpcServerCommand;
use Humus\AmqpBundle\Command\PublishMessageCommand;
use Humus\AmqpBundle\Command\PurgeQueueCommand;
use Humus\AmqpBundle\Command\SetupFabricCommand;
use Humus\AmqpBundle\Factory\ConsumerFactory;
use Humus\AmqpBundle\Factory\ExchangeFactory;
use Humus\AmqpBundle\Factory\QueueFactory;
use Humus\AmqpBundle\SetupFabric\FabricService;
use Humus\AmqpBundle\SetupFabric\Tracer\NullFabricTracer;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\Argument\ServiceLocatorArgument;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

class HumusAmqpExtension extends Extension
{

    const QUEUE_TAG     = 'humus.amqp.queue';
    const EXCHANGE_TAG  = 'humus.amqp.exchange';
    const CONSUMER_TAG  = 'humus.amqp.callback_consumer';
    const PRODUCER_TAG  = 'humus.amqp.producer';
    const JSON_RPC_SERVER_TAG  = 'humus.amqp.json_rpc_server';

    /**
     * @var array
     */
    protected $config;

    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * @var Reference[]
     */
    protected $queueReferences = [];

    /**
     * @var Reference[]
     */
    protected $exchangeReferences = [];

    public function getAlias()
    {
        return 'humus';
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $this->config = $config['amqp'];
        $this->container = $container;

        $this->loadConnections();

        $this->loadExchangeFactory();
        $this->loadExchanges();

        $this->loadQueueFactory();
        $this->loadQueues();

        $this->loadConsumerFactory();
        $this->loadCallbackConsumers();
        $this->loadProducers();

        $this->loadBindingRepositories();
        $this->loadCommands();

        $this->loadDeclareService();

        $this->loadJsonRpcClients();
        $this->loadJsonRpcServers();
    }

    protected function loadExchangeFactory(): void
    {
        $this->container
            ->register(ExchangeFactory::class)
            ->setClass(ExchangeFactory::class)
            ->setArguments([new Reference(FabricService::class)]);
    }

    protected function loadQueueFactory(): void
    {
        $this->container
            ->register(QueueFactory::class)
            ->setClass(QueueFactory::class)
            ->setArguments([new Reference(FabricService::class)]);
    }

    protected function loadConsumerFactory(): void
    {
        $this->container
            ->register(ConsumerFactory::class)
            ->setClass(ConsumerFactory::class);
    }

    protected function loadProducers(): void
    {
        $producers = $this->config['producer'];
        foreach ($producers as $name => $options) {
            $this->loadProducer($name, $options);
        }
    }

    protected function loadProducer(string $name, array $options): void
    {
        $attributes = $options['attributes'];
        switch ($options['type']) {
            case 'json':
            case JsonProducer::class:
                $producerClassName = JsonProducer::class;
                break;
            case 'plain':
            case PlainProducer::class:
                $producerClassName = PlainProducer::class;
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown producer type %s requested', $options['type']));
        }

        $exchangeReference = $this->exchangeReferences[$options['exchange']];

        $definition = new Definition($producerClassName);
        $definition
            ->setArguments([$exchangeReference, $attributes])
            ->addTag(self::PRODUCER_TAG , ['producer_name' => $name]);

        $this->container->setDefinition("humus.amqp.producer.$name", $definition);
    }

    protected function loadCallbackConsumers(): void
    {
        $consumers = $this->config['callback_consumer'];
        foreach ($consumers as $name => $options) {
            $this->loadConsumer($name, $options);
        }
    }

    protected function loadConsumer(string $name, array $options): void
    {
        $logger = isset($options['logger']) ? new Reference($options['logger']) : new Definition(NullLogger::class);
        $flushCallback = isset($options['flush_callback']) ? new Reference($options['flush_callback']) : null;
        $errorCallback = isset($options['error_callback']) ? new Reference($options['error_callback']) : null;;
        $deliveryCallback = new Reference($options['delivery_callback']);
        $queueReference = $this->queueReferences[$options['queue']];

        $factoryRef = new Reference(ConsumerFactory::class);

        $definition = new Definition(CallbackConsumer::class);
        $definition
            ->setFactory([$factoryRef, 'create'])
            ->setArguments([
                $queueReference,
                $logger,
                $deliveryCallback,
                $flushCallback,
                $errorCallback,
                $options
            ])
            ->addTag(self::CONSUMER_TAG, ['consumer_name' => $name]);

        $this->container->setDefinition("humus.amqp.callback_consumer.$name", $definition);
    }

    protected function loadQueues(): void
    {
        $connections = $this->config['queue'];
        foreach ($connections as $name => $options) {
            $this->loadQueue($name, $options);
        }
    }

    protected function loadQueue(string $name, array $options): void
    {
        $definition = new Definition(Queue::class);

        $factoryRef = new Reference(QueueFactory::class);
        if ($options['auto_setup_fabric'] ?? false) {
            $definition->setFactory([$factoryRef, 'createSetup']);
            $definition->addMethodCall('setup', [
                0 => $options['auto_setup_exchanges'] ?? false
            ]);
        } else {
            $definition->setFactory([$factoryRef, 'create']);
        }

        $connectionName = $options['connection'];
        if (false !== strpos($connectionName, 'humus.amqp.connection.')) {
            $connectionName = str_replace('humus.amqp.connection.', '', $connectionName);
        }
        $channelService = 'humus.amqp.channel.'.$connectionName;
        $channelReference = new Reference($channelService);

        $queueName = $options['name'] ?? $name;
        $options['name'] = $queueName;

        $definition
            ->setArguments([$channelReference, $options])
            ->addTag(self::QUEUE_TAG, ['queue_name' => $queueName]);

        $this->container->setDefinition("humus.amqp.queue.$name", $definition);
        $this->queueReferences[$queueName] = $this->queueReferences[$name] = new Reference("humus.amqp.queue.$name");
    }

    protected function loadExchanges(): void
    {
        $connections = $this->config['exchange'];
        foreach ($connections as $name => $options) {
            $this->loadExchange($name, $options);
        }
    }

    protected function loadExchange(string $name, array $options): void
    {
        $definition = new Definition(Exchange::class);

        $exchangeFactoryRef = new Reference(ExchangeFactory::class);
        if ($options['auto_setup_fabric'] ?? false) {
            $definition->setFactory([$exchangeFactoryRef, 'createSetup']);
            $definition->addMethodCall('setup');
        } else {
            $definition->setFactory([$exchangeFactoryRef, 'create']);
        }

        $connectionName = $options['connection'];
        if (false !== strpos($connectionName, 'humus.amqp.connection.')) {
            $connectionName = str_replace('humus.amqp.connection.', '', $connectionName);
        }

        $channelService = 'humus.amqp.channel.'.$connectionName;
        $channelReference = new Reference($channelService);

        $exchangeName = $options['name'] ?? $name;;
        $options['name'] = $exchangeName;

        $definition
            ->setArguments([$channelReference, $options])
            ->addTag(self::EXCHANGE_TAG, ['exchange_name' => $exchangeName]);

        $this->container->setDefinition("humus.amqp.exchange.$name", $definition);
        $this->exchangeReferences[$exchangeName] = $this->exchangeReferences[$name] = new Reference("humus.amqp.exchange.$name");
    }

    protected function loadConnections(): void
    {
        $driver = $this->config['driver'];
        $connections = $this->config['connection'];
        foreach ($connections as $name => $options) {
            $connectionDefinition = $this->createConnectionDefinition($driver, $options);
            $this->container->setDefinition("humus.amqp.connection.$name", $connectionDefinition);

            $connectionReference = new Reference("humus.amqp.connection.$name");
            $channelDefinition = new Definition(Channel::class);
            $channelDefinition->setFactory([$connectionReference, 'newChannel']);

            $this->container->setDefinition("humus.amqp.channel.$name", $channelDefinition);
        }
    }

    protected function createConnectionDefinition(string $driver, array $options): Definition
    {
        switch ($driver) {
            case Driver::AMQP_EXTENSION:
                $className = \Humus\Amqp\Driver\AmqpExtension\Connection::class;
                break;
            case Driver::PHP_AMQP_LIB:
            default:
                if (! isset($options['type'])) {
                    throw new \InvalidArgumentException(
                        'For php-amqplib driver a connection type is required'
                    );
                }
                $type = $options['type'];
                unset($options['type']);
                switch ($type) {
                    case 'lazy':
                    case LazyConnection::class:
                        $className = LazyConnection::class;
                        break;
                    case 'lazy_socket':
                    case LazySocketConnection::class:
                        $className = LazySocketConnection::class;
                        break;
                    case 'socket':
                    case SocketConnection::class:
                        $className = SocketConnection::class;
                        break;
                    case 'ssl':
                    case SslConnection::class:
                        $className = SslConnection::class;
                        break;
                    case 'stream':
                    case StreamConnection::class:
                        $className = StreamConnection::class;
                        break;
                    default:
                        throw new \InvalidArgumentException(
                            'Invalid connection type for php-amqplib driver given'
                        );
                }
                break;
        }

        $definition = new Definition($className, [$options]);
        if ($driver === Driver::AMQP_EXTENSION) {
            $definition->addMethodCall('connect');
        }

        return $definition;
    }

    protected function loadCommands(): void
    {
        $this->container
            ->setDefinition(
                CallbackConsumerCommand::class,
                new Definition(CallbackConsumerCommand::class)
            )
            ->setArguments([
                new ServiceLocatorArgument(new TaggedIteratorArgument(self::CONSUMER_TAG, 'consumer_name', null, true))
            ])
            ->addTag('console.command');
        $this->container
            ->setDefinition(
                PurgeQueueCommand::class,
                new Definition(PurgeQueueCommand::class)
            )
            ->setArguments([
                new ServiceLocatorArgument(new TaggedIteratorArgument(self::QUEUE_TAG, 'queue_name', null, true))
            ])
            ->addTag('console.command');
        $this->container
            ->setDefinition(
                PublishMessageCommand::class,
                new Definition(PublishMessageCommand::class)
            )
            ->setArguments([
                new ServiceLocatorArgument(new TaggedIteratorArgument(self::PRODUCER_TAG, 'producer_name', null, true))
            ])
            ->addTag('console.command');
        $this->container
            ->setDefinition(
                SetupFabricCommand::class,
                new Definition(SetupFabricCommand::class)
            )
            ->setArguments([
                new Reference(FabricService::class),
            ])
            ->addTag('console.command');
        $this->container
            ->setDefinition(
                DeleteFabricCommand::class,
                new Definition(DeleteFabricCommand::class)
            )
            ->setArguments([
                new Reference(FabricService::class),
            ])
            ->addTag('console.command');
        $this->container
            ->setDefinition(
                JsonRpcServerCommand::class,
                new Definition(JsonRpcServerCommand::class)
            )
            ->setArguments([
                new ServiceLocatorArgument(new TaggedIteratorArgument(self::JSON_RPC_SERVER_TAG, 'server_name', null, true)),
            ])
            ->addTag('console.command');
    }

    protected function loadBindingRepositories(): void
    {
        $queues = $this->config['queue'];
        $queueBindingRepository = $this->container->setDefinition(
            'humus.amqp.binding_repository.queue',
            new Definition(BindingRepository::class)
        );

        foreach ($queues as $name => $options) {
            $queueName = $options['name'] ?? $name;
            foreach ($options['exchanges'] as $exchangeName => $exchangeOptions) {
                $bindingDefinition = new Definition(Binding::class, [
                    $exchangeName,
                    $exchangeOptions['routing_keys'],
                    $exchangeOptions['bind_arguments'],
                ]);
                $queueBindingRepository->addMethodCall('addBinding', [$queueName, $bindingDefinition]);
            }
        }

        $exchanges = $this->config['exchange'];
        $exchangeBindingRepository = $this->container->setDefinition(
            'humus.amqp.binding_repository.exchange',
            new Definition(BindingRepository::class)
        );

        foreach ($exchanges as $name => $options) {
            $exchangeName = $options['name'] ?? $name;
            foreach ($options['exchange_bindings'] as $bindExchangeName => $exchangeOptions) {
                $bindingDefinition = new Definition(Binding::class, [
                    $bindExchangeName,
                    $exchangeOptions['routing_keys'],
                    $exchangeOptions['bind_arguments'],
                ]);
                $exchangeBindingRepository->addMethodCall('addBinding', [$exchangeName, $bindingDefinition]);
            }
        }
    }

    protected function loadDeclareService(): void
    {
        $this->container->setDefinition(NullFabricTracer::class, new Definition(NullFabricTracer::class));
        $this->container->setDefinition(FabricService::class, new Definition(FabricService::class, [
            new ServiceLocatorArgument(new TaggedIteratorArgument(self::QUEUE_TAG, 'queue_name', null, true)),
            new ServiceLocatorArgument(new TaggedIteratorArgument(self::EXCHANGE_TAG, 'exchange_name', null, true)),
            new Reference('humus.amqp.binding_repository.queue'),
            new Reference('humus.amqp.binding_repository.exchange'),
            new Reference(NullFabricTracer::class),
        ]));
    }

    protected function loadJsonRpcClients(): void
    {
        $clients = $this->config['json_rpc_client'];
        foreach ($clients as $name => $options) {
            $this->loadJsonRpcClient($name, $options);
        }
    }

    protected function loadJsonRpcClient($name, $options): void
    {
        $queueRef = $this->queueReferences[$options['queue']];

        $exchangesRef = [];
        foreach ($options['exchanges'] as $exchange) {
            $exchangesRef[$exchange] = $this->exchangeReferences[$exchange];
        }

        $this->container
            ->setDefinition("humus.amqp.json_rpc_client.$name", new Definition(JsonRpcClient::class))
            ->setArguments([
                $queueRef,
                $exchangesRef,
                $options['wait_micros'],
                $options['app_id']
            ]);
    }

    protected function loadJsonRpcServers(): void
    {
        $servers = $this->config['json_rpc_server'];
        foreach ($servers as $name => $options) {
            $this->loadJsonRpcServer($name, $options);
        }
    }

    protected function loadJsonRpcServer($name, $options): void
    {
        $queueRef = $this->queueReferences[$options['queue']];

        $deliveryCallbackId = $options['delivery_callback'];
        $deliveryCallbackRef = new Reference($deliveryCallbackId);

        $logger = isset($options['logger']) ? new Reference($options['logger']) : new Definition(NullLogger::class);

        $this->container
            ->setDefinition("humus.amqp.json_rpc_server.$name", new Definition(JsonRpcServer::class))
            ->setArguments([
                $queueRef,
                $deliveryCallbackRef,
                $logger,
                $options['idle_timeout'],
                $options['consumer_tag'],
                $options['app_id'],
                $options['return_trace']
            ])
            ->addTag(self::JSON_RPC_SERVER_TAG, ['server_name' => $name]);
    }
}
