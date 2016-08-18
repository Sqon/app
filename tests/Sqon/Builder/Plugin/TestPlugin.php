<?php

namespace Test\Sqon\Builder\Plugin;

use Sqon\Builder\ConfigurationInterface;
use Sqon\Builder\Plugin\PluginInterface;
use Sqon\SqonInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface as SchemaInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * A test plugin.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class TestPlugin implements PluginInterface, SchemaInterface
{
    /**
     * The build configuration manager.
     *
     * @var ConfigurationInterface
     */
    public static $config;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    public static $eventDispatcher;

    /**
     * The Sqon manager.
     *
     * @var SqonInterface
     */
    public static $sqon;

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $root = $tree->root('test');

        $root
            ->children()
                ->arrayNode('list')
                    ->beforeNormalization()
                        ->ifTrue(
                            function ($value) {
                                return !is_array($value);
                            }
                        )
                        ->then(
                            function ($value) {
                                return [$value];
                            }
                        )
                    ->end()
                    ->prototype('scalar')
                        ->cannotBeEmpty()
                        ->isRequired()
                    ->end()
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
        self::$config = $config;
        self::$eventDispatcher = $dispatcher;
        self::$sqon = $sqon;
    }
}
