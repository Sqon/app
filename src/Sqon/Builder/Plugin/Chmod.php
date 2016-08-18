<?php

namespace Sqon\Builder\Plugin;

use Sqon\Builder\ConfigurationInterface;
use Sqon\Event\Subscriber\ChmodSubscriber;
use Sqon\SqonInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface as SchemaInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Registers `ChmodSubscriber` as a Sqon builder plugin.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Chmod implements PluginInterface, SchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $root = $tree->root('chmod');

        $root
            ->beforeNormalization()
                ->ifTrue(
                    function ($value) {
                        return is_integer($value);
                    }
                )
                ->then(
                    function ($value) {
                        return ['mode' => $value];
                    }
                )
            ->end()
            ->children()
                ->scalarNode('mode')
                    ->cannotBeEmpty()
                    ->isRequired()
                ->end()
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
        $dispatcher->addSubscriber(
            new ChmodSubscriber(
                $config->getSettings('chmod')['mode']
            )
        );
    }
}
