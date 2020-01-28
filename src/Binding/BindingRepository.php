<?php

namespace Humus\AmqpBundle\Binding;

class BindingRepository
{
    /**
     * @var Binding[][]
     */
    protected $bindings = [];

    /**
     * @param string $name
     * @param Binding $binding
     */
    public function addBinding($name, Binding $binding)
    {
        $this->bindings[$name] []=  $binding;
    }

    /**
     * @param string $name
     * @return Binding[]
     */
    public function findByName($name)
    {
        return $this->bindings[$name] ?? [];
    }
}
