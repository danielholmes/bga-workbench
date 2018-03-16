<?php

namespace BGAWorkbench\Project;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConfigFileConfiguration implements ConfigurationInterface
{
    const DEFAULT_LINTER_PHP_BIN = 'php';

    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('config')
            ->children()
                ->arrayNode('testDb')
                    ->isRequired()
                    ->children()
                        ->scalarNode('namePrefix')->isRequired()->end()
                        ->scalarNode('user')->isRequired()->end()
                        ->scalarNode('pass')->isRequired()->end()
                    ->end()
                ->end()
                ->booleanNode('useComposer')
                    ->defaultFalse()
                ->end()
                ->scalarNode('linterPhpBin')->defaultValue(self::DEFAULT_LINTER_PHP_BIN)->end()
                ->arrayNode('extraSrc')
                    ->defaultValue([])
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('sftp')
                    ->children()
                        ->scalarNode('host')->isRequired()->end()
                        ->scalarNode('user')->isRequired()->end()
                        ->scalarNode('pass')->isRequired()->end()
                    ->end()
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
