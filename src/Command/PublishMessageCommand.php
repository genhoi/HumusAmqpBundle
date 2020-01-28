<?php

namespace Humus\AmqpBundle\Command;

use Humus\Amqp\Constants;
use Humus\Amqp\Exception;
use Humus\Amqp\Producer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * Class PublishMessageCommand
 * @package Humus\Amqp\Console\Command
 */
class PublishMessageCommand extends Command
{
    /**
     * @var ServiceProviderInterface
     */
    protected $producers;

    /**
     * PurgeQueueCommand constructor.
     * @param ServiceProviderInterface $producers
     */
    public function __construct(ServiceProviderInterface $producers)
    {
        $this->producers = $producers;

        parent::__construct();
    }

    public static function getDefaultName()
    {
        return 'humus-amqp:publish-message';
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Publish a message to an exchange')
            ->setDefinition([
                new InputArgument(
                    'producer',
                    InputArgument::REQUIRED,
                    'name of the producer to use'
                ),
                new InputOption(
                    'message',
                    'm',
                    InputOption::VALUE_REQUIRED,
                    'message to send',
                    ''
                ),
                new InputOption(
                    'transactional',
                    't',
                    InputOption::VALUE_NONE,
                    'whether to use a transaction for message sending'
                ),
                new InputOption(
                    'confirm_select',
                    'c',
                    InputOption::VALUE_NONE,
                    'whether to use a confirm select mode for message sending'
                ),
                new InputOption(
                    'routing_key',
                    'r',
                    InputOption::VALUE_REQUIRED,
                    'routing key to use',
                    ''
                ),
                new InputOption(
                    'arguments',
                    'a',
                    InputOption::VALUE_OPTIONAL,
                    'arguments to add in JSON-format',
                    '{}'
                ),
                new InputOption(
                    'flags',
                    'f',
                    InputOption::VALUE_REQUIRED,
                    'One or more of Constants::AMQP_MANDATORY (1024) and Constants::AMQP_IMMEDIATE (2048).',
                    Constants::AMQP_NOPARAM
                ),
            ])
            ->setHelp('Publish a message to an exchange');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $producerName = $input->getArgument('producer');

        if (! $producerName) {
            $output->writeln('No producer name given');

            return 1;
        }

        if (! $this->producers->has($producerName)) {
            $output->writeln('Producer with name ' . $producerName . ' not found');

            return 1;
        }

        $transactional = $input->getOption('transactional');

        if ($transactional) {
            $transactional = true;
        } else {
            $transactional = false;
        }

        $confirmSelect = $input->getOption('confirm_select');

        if ($confirmSelect) {
            $confirmSelect = true;
        } else {
            $confirmSelect = false;
        }

        if ($confirmSelect && $transactional) {
            $output->writeln('Can only use one of transactional or confirm select');

            return 1;
        }

        $arguments = $input->getOption('arguments');

        $arguments = json_decode($arguments, true);

        if (json_last_error() !== 0) {
            $output->writeln('Cannot decode arguments');

            return 1;
        }

        $producer = $this->producers->get($producerName);

        /* @var Producer $producer */

        if ($transactional) {
            $producer->startTransaction();
        }

        if ($confirmSelect) {
            $producer->confirmSelect();

            $producer->setConfirmCallback(
                function (int $deliveryTag, bool $multiple = false) {
                    return false;
                },
                function (int $deliveryTag, bool $multiple, bool $requeue) {
                    throw new Exception\RuntimeException('Message nacked');
                }
            );
        }

        $producer->publish(
            $input->getOption('message'),
            $input->getOption('routing_key'),
            (int) $input->getOption('flags'),
            $arguments
        );

        if ($transactional) {
            $producer->commitTransaction();
        }

        if ($confirmSelect) {
            try {
                $producer->waitForConfirm(2.0);
            } catch (\Throwable $e) {
                echo get_class($e) . ': ' . $e->getMessage();

                return 1;
            }
        }

        $output->writeln('Message published');

        return 0;
    }
}
