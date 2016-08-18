<?php

namespace Sqon\Builder\Plugin;

use Sqon\Builder\ConfigurationInterface;
use Sqon\Event\Subscriber\ReplaceSubscriber;
use Sqon\SqonInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface as SchemaInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Registers the `ReplaceSubscriber` as a Sqon builder plugin.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Replace implements PluginInterface, SchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $root = $tree->root('replace');

        $patterns = function ($type) {
            $tree = new TreeBuilder();
            $root = $tree->root($type);

            $root
                ->prototype('array')
                    ->children()
                        ->scalarNode('path')
                            ->cannotBeEmpty()
                            ->isRequired()
                        ->end()
                        ->scalarNode('pattern')
                            ->cannotBeEmpty()
                            ->isRequired()
                        ->end()
                        ->scalarNode('replacement')
                            ->defaultValue('')
                        ->end()
                    ->end()
                ->end()
            ;

            return $root;
        };

        $root
            ->beforeNormalization()
                ->ifArray()
                ->then(
                    function ($values) {
                        $fixed = [
                            'all' => [],
                            'path' => [],
                            'pattern' => []
                        ];

                        if (isset($values['all'])) {
                            foreach ($values['all'] as $pattern => $replacement) {
                                $fixed['all'][] = [
                                    'pattern' => $pattern,
                                    'replacement' => $replacement
                                ];
                            }
                        }

                        foreach (['path', 'pattern'] as $type) {
                            if (isset($values[$type])) {
                                foreach ($values[$type] as $path => $replacements) {
                                    foreach ($replacements as $pattern => $replacement) {
                                        $fixed[$type][] = [
                                            'path' => $path,
                                            'pattern' => $pattern,
                                            'replacement' => $replacement
                                        ];
                                    }
                                }
                            }
                        }

                        return $fixed;
                    }
                )
            ->end()
            ->children()
                ->arrayNode('all')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('pattern')
                                ->cannotBeEmpty()
                                ->isRequired()
                            ->end()
                            ->scalarNode('replacement')
                                ->defaultValue('')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->append($patterns('path'))
                ->append($patterns('pattern'))
            ->end()
        ;

        return $tree;
    }

    /**
     * {@inheritdoc}
     */
    public function register(
        EventDispatcherInterface $dispatcher,
        ConfigurationInterface $config,
        SqonInterface $sqon
    ) {
        $subscriber = new ReplaceSubscriber();

        foreach ($config->getSettings('replace') as $type => $patterns) {
            switch ($type) {
                case 'all':
                    foreach ($patterns as $pattern) {
                        $subscriber->replaceAll(
                            $pattern['pattern'],
                            $pattern['replacement']
                        );
                    }

                    break;

                case 'path':
                    foreach ($patterns as $pattern) {
                        $subscriber->replaceByPath(
                            $pattern['path'],
                            $pattern['pattern'],
                            $pattern['replacement']
                        );
                    }

                    break;

                case 'pattern':
                    foreach ($patterns as $pattern) {
                        $subscriber->replaceByPattern(
                            $pattern['path'],
                            $pattern['pattern'],
                            $pattern['replacement']
                        );
                    }

                    break;
            }
        }

        $dispatcher->addSubscriber($subscriber);
    }
}
