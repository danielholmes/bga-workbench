<?php

namespace BGAWorkbench\Validate;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class StateConfiguration implements ConfigurationInterface
{
    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('machinestates')
            ->prototype('array')
                ->children()
                    ->scalarNode('name')->isRequired()->end()
                    ->scalarNode('description')->end()
                    ->scalarNode('descriptionmyturn')->end()
                    ->scalarNode('action')->end()
                    ->arrayNode('possibleactions')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('transitions')
                        ->prototype('scalar')->end()
                    ->end()
                    ->scalarNode('args')->end()
                    ->booleanNode('updateGameProgression')->end()
                    ->enumNode('type')
                        ->isRequired()
                        ->values(['activeplayer', 'multipleactiveplayer', 'game', 'manager'])
                    ->end()
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
