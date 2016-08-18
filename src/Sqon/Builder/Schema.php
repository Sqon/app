<?php

namespace Sqon\Builder;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface as SchemaInterface;

/**
 * Defines the schema for the build configuration settings.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Schema implements SchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $root = $tree->root('sqon');

        $root
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('bootstrap')
                    ->defaultNull()
                ->end()
                ->scalarNode('compression')
                    ->beforeNormalization()
                        ->ifNotInArray(['BZIP2', 'GZIP', 'NONE'])
                        ->thenInvalid('The compression mode is not valid.')
                    ->end()
                    ->cannotBeEmpty()
                    ->defaultValue('NONE')
                ->end()
                ->scalarNode('main')
                    ->defaultNull()
                ->end()
                ->scalarNode('output')
                    ->cannotBeEmpty()
                    ->defaultValue('project.sqon')
                ->end()
                ->arrayNode('paths')
                    ->cannotBeEmpty()
                    ->prototype('scalar')
                        ->cannotBeEmpty()
                    ->end()
                ->end()
                ->append($this->getPlugins())
                ->scalarNode('shebang')
                    ->defaultNull()
                ->end()
            ->end()
        ;

        return $tree;
    }

    /**
     * Creates the plugin autoload schema.
     *
     * @return ArrayNodeDefinition The autoload schema.
     */
    private function getPluginAutoload()
    {
        $tree = new TreeBuilder();
        $root = $tree->root('autoload');

        $fix = function ($prefixes) {
            $fixed = [];

            foreach ($prefixes as $prefix => $paths) {
                $fixed[] = [
                    'prefix' => $prefix,
                    'paths' => (array) $paths
                ];
            }

            return $fixed;
        };

        $root
            ->children()
                ->arrayNode('classmap')
                    ->prototype('scalar')
                        ->cannotBeEmpty()
                    ->end()
                ->end()

                ->arrayNode('files')
                    ->prototype('scalar')
                        ->cannotBeEmpty()
                    ->end()
                ->end()

                ->arrayNode('psr0')
                    ->beforeNormalization()
                        ->ifArray()
                        ->then($fix)
                    ->end()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('prefix')
                                ->cannotBeEmpty()
                                ->isRequired()
                            ->end()
                            ->arrayNode('paths')
                                ->prototype('scalar')
                                    ->cannotBeEmpty()
                                    ->isRequired()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('psr4')
                    ->beforeNormalization()
                        ->ifArray()
                        ->then($fix)
                    ->end()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('prefix')
                                ->cannotBeEmpty()
                                ->isRequired()
                            ->end()
                            ->arrayNode('paths')
                                ->prototype('scalar')
                                    ->cannotBeEmpty()
                                    ->isRequired()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $root;
    }

    /**
     * Creates the plugin schema.
     *
     * @return ArrayNodeDefinition The plugin schema.
     */
    private function getPlugins()
    {
        $tree = new TreeBuilder();
        $root = $tree->root('plugins');

        $root
            ->prototype('array')
                ->children()
                    ->append($this->getPluginAutoload())
                    ->scalarNode('class')
                        ->cannotBeEmpty()
                        ->isRequired()
                    ->end()
                ->end()
            ->end()
        ;

        return $root;
    }
}
