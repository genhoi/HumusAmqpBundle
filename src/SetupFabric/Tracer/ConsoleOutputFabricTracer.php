<?php

namespace Humus\AmqpBundle\SetupFabric\Tracer;

use Symfony\Component\Console\Output\OutputInterface;

class ConsoleOutputFabricTracer implements FabricTracerInterface
{
    protected OutputInterface $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function declaredQueue(string $queueName): void
    {
        $this->output->writeln("Queue <info>$queueName</info> declared");
    }

    public function declaredExchange(string $exchangeName): void
    {
        $this->output->writeln("Exchange <info>$exchangeName</info> declared");
    }

    public function bindQueue(string $queueName, string $exchangeName, string $routingKey): void
    {
        $this->output->writeln("Queue <info>$queueName</info> bind to <info>$exchangeName</info> with routing key <info>'$routingKey'</info>");
    }

    public function bindExchange(string $exchangeName, string $boundExchangeName, string $routingKey): void
    {
        $this->output->writeln("Exchange <info>$exchangeName</info> bind to <info>$boundExchangeName</info> with routing key <info>'$routingKey'</info>");
    }

    public function deleteQueue(string $queueName): void
    {
        $this->output->writeln("Queue <info>$queueName</info> deleted");
    }

    public function deleteExchange(string $exchangeName): void
    {
        $this->output->writeln("Exchange <info>$exchangeName</info> deleted");
    }
}
