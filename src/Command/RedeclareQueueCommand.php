<?php

namespace Humus\AmqpBundle\Command;

use Humus\Amqp\Constants;
use Humus\Amqp\Queue;
use Humus\AmqpBundle\Binding\BindingRepository;
use Humus\AmqpBundle\SetupFabric\FabricService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;

class RedeclareQueueCommand extends Command
{

    /**
     * @var ServiceProviderInterface
     */
    protected $queues;

    /**
     * @var BindingRepository
     */
    protected $queueBindingRepository;

    /**
     * @var FabricService
     */
    protected $fabric;

    public function __construct(
        ServiceProviderInterface $queues,
        BindingRepository $queueBindingRepository,
        FabricService $fabric
    ) {
        $this->queues = $queues;
        $this->queueBindingRepository = $queueBindingRepository;
        $this->fabric = $fabric;

        parent::__construct();
    }

    public static function getDefaultName()
    {
        return 'humus-amqp:redeclare-queue';
    }

    protected function configure()
    {
        $help = <<<'EOF'
Redeclare queue command
EOF;

        $this
            ->setDescription('Redeclare queue command')
            ->setHelp($help);

        $this->addArgument(
            'queue',
            InputArgument::REQUIRED,
            'Queue name'
        );

        parent::configure();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queueName = $input->getArgument('queue');
        $tmpQueueName = $queueName.'_tmp_'.uniqid();

        if (!$this->queues->has($queueName)) {
            $output->writeln("Queue with name '$queueName' not found");

            return 1;
        }
        $queue = $this->queues->get($queueName); /** @var Queue $queue */

        $connection = $queue->getConnection();
        $channel = $connection->newChannel();
        $tmpQueue = $channel->newQueue();
        $tmpQueue->setName($tmpQueueName);
        $tmpQueue->setFlags(Constants::AMQP_DURABLE);
        $tmpQueue->declareQueue();
        $output->writeln("Declared queue {$tmpQueue->getName()}");

        $tmpExchange = $channel->newExchange();
        $tmpExchange->setName($tmpQueueName);
        $tmpExchange->setType('direct');
        $tmpExchange->declareExchange();
        $output->writeln("Declared exchange {$tmpExchange->getName()}");

        $tmpQueue->bind($tmpExchange->getName());
        $output->writeln("Bind queue {$tmpQueue->getName()} to exchange {$tmpExchange->getName()} with routing key ''");

        $bindings = $this->queueBindingRepository->findByName($queueName);
        foreach ($bindings as $binding) {
            foreach ($binding->getRoutingKeys() as $routingKey) {
                $tmpQueue->bind($binding->getExchangeName(), $routingKey, $binding->getArgs());
                $output->writeln("Bind queue {$tmpQueue->getName()} to exchange {$binding->getExchangeName()} with routing key '{$routingKey}'");
            }
        }

        foreach ($bindings as $binding) {
            foreach ($binding->getRoutingKeys() as $routingKey) {
                $queue->unbind($binding->getExchangeName(), $routingKey, $binding->getArgs());
                $output->writeln("Unbind queue {$queue->getName()} form exchange {$binding->getExchangeName()} with routing key '{$routingKey}'");
            }
        }

        $output->writeln("Move messages from {$queue->getName()} to queue {$tmpQueue->getName()}");
        while ($envelop = $queue->get()) {
            $tmpExchange->publish($envelop->getBody());
            $queue->ack($envelop->getDeliveryTag());
        }

        $queue->delete();
        $output->writeln("Delete queue {$queue->getName()}");

        $this->fabric->setupQueue($queue, false);
        $output->writeln("Setup queue {$queue->getName()}");

        foreach ($bindings as $binding) {
            foreach ($binding->getRoutingKeys() as $routingKey) {
                $tmpQueue->unbind($binding->getExchangeName(), $routingKey, $binding->getArgs());
                $output->writeln("Unbind queue {$tmpQueue->getName()} form exchange {$binding->getExchangeName()} with routing key '{$routingKey}'");
            }
        }
        $tmpQueue->unbind($tmpExchange->getName());
        $output->writeln("Unbind queue {$tmpQueue->getName()} from exchange {$tmpExchange->getName()}");

        $queue->bind($tmpExchange->getName());
        $output->writeln("Bind queue {$queue->getName()} to exchange {$tmpExchange->getName()} with routing key ''");

        $output->writeln("Move messages from {$tmpQueue->getName()} to queue {$queue->getName()}");
        while ($envelop = $tmpQueue->get()) {
            $tmpExchange->publish($envelop->getBody());
            $tmpQueue->ack($envelop->getDeliveryTag());
        }

        $tmpQueue->delete();
        $output->writeln("Delete queue {$tmpQueue->getName()}");

        $tmpExchange->delete();
        $output->writeln("Delete exchange {$tmpExchange->getName()}");

        return 0;
    }
}
