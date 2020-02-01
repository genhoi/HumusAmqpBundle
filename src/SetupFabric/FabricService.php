<?php

namespace Humus\AmqpBundle\SetupFabric;

use Humus\Amqp\Exchange;
use Humus\Amqp\Queue;
use Humus\AmqpBundle\Binding\BindingRepository;
use Humus\AmqpBundle\SetupFabric\Tracer\FabricTracerInterface;
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
     * @var FabricTracerInterface
     */
    protected $tracer;

    /**
     * DeclareService constructor.
     * @param ServiceProviderInterface $queues
     * @param ServiceProviderInterface $exchanges
     * @param BindingRepository $queueBindingRepository
     * @param BindingRepository $exchangeBindingRepository
     * @param FabricTracerInterface $tracer
     */
    public function __construct(
        ServiceProviderInterface $queues,
        ServiceProviderInterface $exchanges,
        BindingRepository $queueBindingRepository,
        BindingRepository $exchangeBindingRepository,
        FabricTracerInterface $tracer
    ) {
        $this->queuesLocator = $queues;
        $this->exchangesLocator = $exchanges;
        $this->queueBindingRepository = $queueBindingRepository;
        $this->exchangeBindingRepository = $exchangeBindingRepository;
        $this->tracer = $tracer;
    }

    /**
     * @param FabricTracerInterface $declareTracer
     * @return FabricService
     */
    public function withTracer(FabricTracerInterface $declareTracer)
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
     * @param Queue $queue
     * @param bool $setupExchanges
     *
     * @return void
     *
     * @throws \Humus\Amqp\Exception\ChannelException
     * @throws \Humus\Amqp\Exception\QueueException
     */
    public function setupQueue(Queue $queue, bool $setupExchanges)
    {
        $queueName = $queue->getName();

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
                $bindingExchange = $this->exchangesLocator->get($exchangeName);
                $this->setupExchange($bindingExchange);
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
    public function setupExchange(Exchange $exchange)
    {
        $exchangeName = $exchange->getName();

        $exchange->declareExchange();
        $this->tracer->declaredExchange($exchangeName);

        $arguments = $exchange->getArguments();
        if (isset($arguments['alternate-exchange'])) {
            // auto setup fabric alternate exchange
            $alternateExchange = $this->exchangesLocator->get($arguments['alternate-exchange']);
            $this->setupExchange($alternateExchange);
        }
        $bindings = $this->exchangeBindingRepository->findByName($exchangeName);
        foreach ($bindings as $binding) {
            $bindingExchange = $this->exchangesLocator->get($binding->getExchangeName());
            $this->setupExchange($bindingExchange);
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
            $exchange = $this->exchangesLocator->get($name);
            $this->setupExchange($exchange);
        }
        foreach ($this->queuesLocator->getProvidedServices() as $name => $queue) {
            $queue = $this->queuesLocator->get($name);
            $this->setupQueue($queue, false);
        }
    }

    public function delete()
    {
        foreach ($this->exchangesLocator->getProvidedServices() as $name => $type) {
            /** @var Exchange $exchange */
            $exchange = $this->exchangesLocator->get($name);
            $exchange->delete();
            $this->tracer->deleteExchange($exchange->getName());
        }
        foreach ($this->queuesLocator->getProvidedServices() as $name => $type) {
            /** @var Queue $queue */
            $queue = $this->queuesLocator->get($name);
            $queue->delete();
            $this->tracer->deleteQueue($queue->getName());
        }
    }
}
