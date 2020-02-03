<?php

namespace Humus\AmqpBundle\Binding;

class BindingRepository
{
    /**
     * @var Binding[][]
     */
    protected $bindings = [];

    public function addBinding(string $name, Binding $binding): void
    {
        $this->bindings[$name] []=  $binding;
    }

    /**
     * @param string $name
     * @return Binding[]
     */
    public function findByName(string $name): array
    {
        return $this->bindings[$name] ?? [];
    }
}
