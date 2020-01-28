<?php

namespace Humus\AmqpBundle\SetupFabric;

use Humus\Amqp\Exchange;
use Humus\Amqp\Queue;
use Humus\AmqpBundle\Binding\BindingRepository;
use Humus\AmqpBundle\SetupFabric\Tracer\SetupFabricTracerInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;

class FabricService
{

    /**
     * @var ServiceProviderInterface
     */
    protected $queuesLocator;

    /**
     * @var ServiceProviderInterface
     */
    protected $exchangesLocator;

    /**
     * @var BindingRepository
     */
    protected $queueBindingRepository;

    /**
     * @var BindingRepository
     */
    protected $exchangeBindingRepository;

    /**
     * @var SetupFabricTracerInterface
     */
    protected $tracer;

    /**
     * DeclareService constructor.
     * @param ServiceProviderInterface $queues
     * @param ServiceProviderInterface $exchanges
     * @param BindingRepository $queueBindingRepository
     * @param BindingRepository $exchangeBindingRepository
     * @param SetupFabricTracerInterface $tracer
     */
    public function __construct(
        ServiceProviderInterface $queues,
        ServiceProviderInterface $exchanges,
        BindingRepository $queueBindingRepository,
        BindingRepository $exchangeBindingRepository,
        SetupFabricTracerInterface $tracer
    ) {
        $this->queuesLocator = $queues;
        $this->exchangesLocator = $exchanges;
        $this->queueBindingRepository = $queueBindingRepository;
        $this->exchangeBindingRepository = $exchangeBindingRepository;
        $this->tracer = $tracer;
    }

    /**
     * @param SetupFabricTracerInterface $declareTracer
     * @return FabricService
     */
    public function withTracer(SetupFabricTracerInterface $declareTracer)
    {
        return new self(
            $this->queuesLocator,
            $this->exchangesLocator,
            $this->queueBindingRepository,
            $this->exchangeBindingRepository,
            $declareTracer
        );
    }

    /**
     * @param string $queueName
     * @param bool $setupExchanges
     *
     * @return void
     *
     * @throws \Humus\Amqp\Exception\ChannelException
     * @throws \Humus\Amqp\Exception\QueueException
     */
    public function setupQueue(string $queueName, bool $setupExchanges)
    {
        if (!$this->queuesLocator->has($queueName)) {
            return;
        }

        $queue = $this->queuesLocator->get($queueName); /** @var $queue Queue */
        $queue->declareQueue();
        $this->tracer->declaredQueue($queueName);

        $bindings = $this->queueBindingRepository->findByName($queueName);
        if ($setupExchanges) {
            $exchanges = [];

            $arguments = $queue->getArguments();
            if (isset($arguments['x-dead-letter-exchange'])) {
                $exchanges []= $arguments['x-dead-letter-exchange'];
            }

            foreach ($bindings as $binding) {
                $exchanges []= $binding->getExchangeName();
            }

            foreach ($exchanges as $exchangeName) {
                $this->setupExchange($exchangeName);
            }
        }

        foreach ($bindings as $binding) {
            foreach ($binding->getRoutingKeys() as $routingKey) {
                $queue->bind($binding->getExchangeName(), $routingKey, $binding->getArgs());
                $this->tracer->bindQueue($queue->getName(), $binding->getExchangeName(), $routingKey);
            }
        }
    }

    /**
     * @param string $exchangeName
     *
     * @return void
     */
    public function setupExchange(string $exchangeName)
    {
        if (!$this->exchangesLocator->has($exchangeName)) {
            return;
        }
        
        $exchange = $this->exchangesLocator->get($exchangeName); /** @var $exchange Exchange */
        $exchange->declareExchange();
        $this->tracer->declaredExchange($exchangeName);

        $arguments = $exchange->getArguments();
        if (isset($arguments['alternate-exchange'])) {
            // auto setup fabric alternate exchange
            $this->setupExchange($arguments['alternate-exchange']);
        }
        $bindings = $this->exchangeBindingRepository->findByName($exchangeName);
        foreach ($bindings as $binding) {
            $this->setupExchange($binding->getExchangeName());
        }

        foreach ($bindings as $binding) {
            foreach ($binding->getRoutingKeys() as $routingKey) {
                $exchange->bind($binding->getExchangeName(), $routingKey, $binding->getArgs());
                $this->tracer->bindExchange($exchange->getName(), $binding->getExchangeName(), $routingKey);
            }
        }
    }

    /**
     * @throws \Humus\Amqp\Exception\ChannelException
     * @throws \Humus\Amqp\Exception\QueueException
     */
    public function setup()
    {
        foreach ($this->exchangesLocator->getProvidedServices() as $name => $exchange) {
            $this->setupExchange($name);
        }
        foreach ($this->queuesLocator->getProvidedServices() as $name => $queue) {
            $this->setupQueue($name, false);
        }
    }

    public function delete()
    {
        foreach ($this->exchangesLocator->getProvidedServices() as $name => $type) {
            /** @var Exchange $exchange */
            $exchange = $this->exchangesLocator->get($name);
            $exchange->delete();
        }
        foreach ($this->queuesLocator->getProvidedServices() as $name => $type) {
            /** @var Queue $queue */
            $queue = $this->queuesLocator->get($name);
            $queue->delete();
        }
    }
}
