<?php

namespace Humus\AmqpBundle\Command;

use Humus\AmqpBundle\SetupFabric\FabricService;
use Humus\AmqpBundle\SetupFabric\Tracer\ConsoleOutputFabricTracer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetupFabricCommand extends Command
{
    /**
     * @var FabricService
     */
    protected $setupFabricService;

    /**
     * SetupFabricCommand constructor.
     *
     * @param FabricService $declareService
     */
    public function __construct(FabricService $declareService)
    {
        $this->setupFabricService = $declareService;

        parent::__construct();
    }

    public static function getDefaultName()
    {
        return 'humus-amqp:setup-fabric';
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Declares all AMQP exchanges and queues')
            ->setHelp('Declares all AMQP exchanges and queues');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setupFabricService
            ->withTracer(new ConsoleOutputFabricTracer($output))
            ->setup();

        $output->writeln('Done');

        return 0;
    }
}
