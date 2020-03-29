<?php

namespace Humus\AmqpBundle\Command;

use Humus\Amqp\Consumer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;

class JsonRpcServerCommand extends Command
{
    protected ServiceProviderInterface $servers;

    public static function getDefaultName()
    {
        return 'humus-amqp:json-rpc-server';
    }

    public function __construct(ServiceProviderInterface $servers)
    {
        $this->servers = $servers;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Start a JSON-RPC server')
            ->setAliases(['humus-amqp:json_rpc_server'])
            ->setDefinition([
                new InputArgument(
                    'name',
                    InputArgument::OPTIONAL,
                    'name of the JSON-RPC server to start'
                ),
                new InputOption(
                    'name',
                    'c',
                    InputOption::VALUE_REQUIRED,
                    'name of the JSON-RPC server to start'
                ),
                new InputOption(
                    'amount',
                    'a',
                    InputOption::VALUE_OPTIONAL,
                    'amount of messages to consume',
                    0
                ),
            ])
            ->setHelp('Start a JSON-RPC server');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $serverName = $input->getArgument('name');
        if (empty($serverName)) {
            $serverName = $input->getOption('name');
        }

        if (!$serverName) {
            $output->writeln('No JSON-RPC server given');

            return 1;
        }

        if (!$this->servers->has($serverName)) {
            $output->writeln("No JSON-RPC server with name '$serverName' found");

            return 1;
        }

        $jsonRpcServer = $this->servers->get($serverName); /* @var Consumer $jsonRpcServer */
        $jsonRpcServer->consume((int) $input->getOption('amount'));

        return 0;
    }
}
