<?php

namespace Humus\AmqpBundle\DependencyInjection;

use Humus\Amqp\Driver\Driver;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $humus = new TreeBuilder('humus');
        $amqp = $humus->getRootNode()->children()->arrayNode('amqp');
        $amqp->children()
            ->enumNode('driver')->isRequired()->values(Driver::getValues())->end()
            ->append($this->createConnection())
            ->append($this->createCallbackConsumer())
            ->append($this->createExchange())
            ->append($this->createQueue())
            ->append($this->createProducer())
            ->append($this->createJsonRpcClient())
            ->append($this->createJsonRpcServer())
        ->end();


        return $humus;
    }

    /**
     * @param $name
     * @return ArrayNodeDefinition[]
     */
    protected function createNodeWithArrayPrototype($name): array
    {
        $node = new ArrayNodeDefinition($name);
        $prototype = $node
            ->useAttributeAsKey('key')
            ->canBeUnset()
            ->arrayPrototype();

        return [$node, $prototype];
    }

    protected function createConnection() : ArrayNodeDefinition
    {
        [$node, $prototype] = $this->createNodeWithArrayPrototype('connection');

        $prototype->children()
            ->scalarNode('host')->defaultValue('localhost')->end()
            ->integerNode('port')->defaultValue(5672)->end()
            ->scalarNode('login')->defaultValue('guest')->end()
            ->scalarNode('password')->defaultValue('guest')->end()
            ->scalarNode('vhost')->defaultValue('/')->end()
            ->booleanNode('persistent')->defaultFalse()->end()
            ->floatNode('connect_timeout')->defaultValue(1.0)->end()
            ->floatNode('read_timeout')->defaultValue(1.0)->end()
            ->floatNode('write_timeout')->defaultValue(1.0)->end()
            ->integerNode('heartbeat')->defaultValue(0)->end()
            ->scalarNode('type')->end()
        ->end();

        return $node;
    }

    protected function createCallbackConsumer() : ArrayNodeDefinition
    {
        [$node, $prototype] = $this->createNodeWithArrayPrototype('callback_consumer');

        $prototype->children()
            ->scalarNode('queue')->isRequired()->end()
            ->scalarNode('delivery_callback')->isRequired()->end()
            ->scalarNode('error_callback')->defaultNull()->end()
            ->scalarNode('flush_callback')->defaultNull()->end()
            ->scalarNode('logger')->defaultNull()->end()
            ->scalarNode('consumer_tag')->defaultNull()->end()
            ->scalarNode('idle_timeout')->defaultValue(5.0)->end()
            ->arrayNode('qos')->children()
                ->scalarNode('prefetch_count')->defaultValue(3)->end()
                ->scalarNode('prefetch_size')->defaultValue(0)->end()
            ->end()
        ->end();

        return $node;
    }

    protected function createQueue() : ArrayNodeDefinition
    {
        [$node, $prototype] = $this->createNodeWithArrayPrototype('queue');

        $prototype->children()
            ->scalarNode('name')->defaultNull()->end()
            ->scalarNode('connection')->defaultValue('default')->end()
            ->booleanNode('durable')->defaultTrue()->end()
            ->booleanNode('passive')->defaultFalse()->end()
            ->booleanNode('exclusive')->defaultFalse()->end()
            ->booleanNode('auto_delete')->defaultFalse()->end()
            ->booleanNode('auto_setup_fabric')->defaultFalse()->end()
            ->booleanNode('auto_setup_exchanges')->defaultFalse()->end()
            ->arrayNode('arguments')->defaultValue([])
                ->prototype('scalar')->end()
            ->end()
            ->append($this->createQueueBindings())
        ->end();

        return $node;
    }

    protected function createQueueBindings(): ArrayNodeDefinition
    {
        [$exchangesNode, $exchangePrototype] = $this->createNodeWithArrayPrototype('exchanges');
        $exchangePrototype->children()
            ->arrayNode('routing_keys')
                ->canBeUnset()
                ->defaultValue([''])
                ->prototype('scalar')->end()
            ->end()
            ->arrayNode('bind_arguments')
                ->canBeUnset()
                ->defaultValue([])
                ->prototype('scalar')->end()
            ->end()
        ->end();

        $exchangesNode->canBeUnset();

        return $exchangesNode;
    }

    protected function createExchangeBindings(): ArrayNodeDefinition
    {
        [$exchangesNode, $exchangePrototype] = $this->createNodeWithArrayPrototype('exchange_bindings');
        $exchangePrototype->children()
            ->arrayNode('routing_keys')
                ->canBeUnset()
                ->defaultValue([])
                ->prototype('scalar')->end()
            ->end()
            ->arrayNode('bind_arguments')
                ->canBeUnset()
                ->defaultValue([])
                ->prototype('scalar')->end()
            ->end()
        ->end();

        $exchangesNode->canBeUnset();

        return $exchangesNode;
    }

    protected function createExchange(): ArrayNodeDefinition
    {
        [$node, $prototype] = $this->createNodeWithArrayPrototype('exchange');

        $prototype->children()
            ->scalarNode('name')->defaultNull()->end()
            ->arrayNode('arguments')->defaultValue([])
                ->prototype('scalar')->end()
            ->end()
            ->booleanNode('auto_delete')->defaultFalse()->end()
            ->booleanNode('passive')->defaultFalse()->end()
            ->booleanNode('durable')->defaultTrue()->end()
            ->booleanNode('internal')->defaultFalse()->end()
            ->scalarNode('type')->defaultValue('direct')->end()
            ->booleanNode('auto_setup_fabric')->defaultFalse()->end()
            ->scalarNode('connection')->defaultValue('default')->end()
            ->append($this->createExchangeBindings())
        ->end();

        return $node;
    }

    protected function createProducer(): ArrayNodeDefinition
    {
        [$node, $prototype] = $this->createNodeWithArrayPrototype('producer');

        $prototype->children()
            ->scalarNode('exchange')->isRequired()->end()
            ->enumNode('type')->values(['json', 'plain'])->end()
            ->arrayNode('attributes')
                ->variablePrototype()->end()
            ->end()
        ->end();

        return $node;
    }

    protected function createJsonRpcClient(): ArrayNodeDefinition
    {
        [$node, $prototype] = $this->createNodeWithArrayPrototype('json_rpc_client');

        $prototype->children()
            ->scalarNode('queue')->isRequired()->end()
            ->scalarNode('wait_micros')->defaultValue(1000)->end()
            ->scalarNode('app_id')->defaultValue('')->end()
            ->arrayNode('exchanges')
                ->requiresAtLeastOneElement()
                ->isRequired()
                ->scalarPrototype()->end()
            ->end()
        ->end();

        return $node;
    }

    protected function createJsonRpcServer(): ArrayNodeDefinition
    {
        [$node, $prototype] = $this->createNodeWithArrayPrototype('json_rpc_server');

        $prototype->children()
            ->scalarNode('queue')->isRequired()->end()
            ->scalarNode('delivery_callback')->isRequired()->end()
            ->floatNode('idle_timeout')->isRequired()->end()
            ->scalarNode('consumer_tag')->defaultValue('')->end()
            ->scalarNode('app_id')->defaultValue('')->end()
            ->booleanNode('return_trace')->defaultFalse()->end()
            ->scalarNode('logger')->defaultNull()->end()
        ->end();

        return $node;
    }
}
