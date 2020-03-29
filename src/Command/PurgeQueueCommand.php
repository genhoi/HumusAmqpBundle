<?php

namespace Humus\AmqpBundle\Command;

use Humus\Amqp\Queue;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;

class PurgeQueueCommand extends Command
{
    protected ServiceProviderInterface $queues;

    public function __construct(ServiceProviderInterface $queues)
    {
        $this->queues = $queues;

        parent::__construct();
    }

    public static function getDefaultName()
    {
        return 'humus-amqp:purge-queue';
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Purges a queue')
            ->setDefinition([
                new InputArgument(
                    'name',
                    InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                    'name of the queue to purge'
                ),
                new InputOption(
                    'name',
                    'p',
                    InputOption::VALUE_REQUIRED,
                    'name of the queue to purge'
                ),
            ])
            ->setHelp('Purges a queue');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queueNames = $input->getArgument('name');
        if (empty($queueNames) && $queueName = $input->getOption('name')) {
            $queueNames = [$queueName];
        }
        foreach ($queueNames as $name) {
            if (!$this->queues->has($name)) {
                $output->writeln("Queue with name '$name' not found");

                return 1;
            }

            $queue = $this->queues->get($name); /** @var Queue $queue */
            $queue->purge();
        }

        return 0;
    }
}
