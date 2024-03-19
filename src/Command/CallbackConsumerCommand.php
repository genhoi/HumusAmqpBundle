<?php

namespace Humus\AmqpBundle\Command;

use Humus\Amqp\Consumer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;

class CallbackConsumerCommand extends Command
{
    protected ServiceProviderInterface $consumers;

    public static function getDefaultName(): ?string
    {
        return 'humus-amqp:consumer';
    }

    public function __construct(ServiceProviderInterface $consumers)
    {
        $this->consumers = $consumers;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Start a consumer')
            ->setDefinition([
                new InputArgument(
                    'name',
                    InputArgument::OPTIONAL,
                    'name of the consumer to start'
                ),
                new InputOption(
                    'name',
                    'c',
                    InputOption::VALUE_REQUIRED,
                    'name of the consumer to start'
                ),
                new InputOption(
                    'amount',
                    'a',
                    InputOption::VALUE_OPTIONAL,
                    'amount of messages to consume',
                    0
                ),
            ])
            ->setHelp('Start a consumer');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $consumerName = $input->getArgument('name');
        if (empty($consumerName)) {
            $consumerName = $input->getOption('name');
        }

        if (!$consumerName) {
            $output->writeln('No consumer given');

            return 1;
        }

        if (!$this->consumers->has($consumerName)) {
            $output->writeln("No consumer with name '$consumerName' found");

            return 1;
        }

        $callbackConsumer = $this->consumers->get($consumerName); /* @var Consumer $callbackConsumer */
        $callbackConsumer->consume((int) $input->getOption('amount'));

        return 0;
    }
}
