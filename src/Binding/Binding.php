<?php

namespace Humus\AmqpBundle\Binding;

class Binding
{
    /**
     * @var string
     */
    protected $exchangeName;

    /**
     * @var string[]
     */
    protected $routingKeys;

    /**
     * @var array
     */
    protected $args;

    /**
     * Binding constructor.
     *
     * @param string   $exchangeName
     * @param string[] $routingKeys
     * @param array    $args
     */
    public function __construct(string $exchangeName, array $routingKeys = [], array $args = [])
    {
        $this->exchangeName = $exchangeName;
        $this->routingKeys = $routingKeys;
        $this->args = $args;
    }

    /**
     * @return string
     */
    public function getExchangeName(): string
    {
        return $this->exchangeName;
    }

    /**
     * @return string[]
     */
    public function getRoutingKeys(): array
    {
        return $this->routingKeys;
    }

    /**
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }
}
