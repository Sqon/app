<?php

namespace Sqon\Builder\Plugin;

use Sqon\Builder\ConfigurationInterface;
use Sqon\SqonInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Defines the public interface for a Sqon builder plugin.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
interface PluginInterface
{
    /**
     * Registers a plugin with the Sqon builder.
     *
     * A plugin is expected to register one or more listeners and subscribers
     * with the event dispatcher. The `$config` manager is provided in order
     * for the plugin to retrieve any needed settings. The `$sqon` manager is
     * also provided in case the changes can be performed directly to the Sqon.
     *
     * ```php
     * $plugin->register($dispatcher, $config, $sqon);
     * ```
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     * @param ConfigurationInterface   $config     The build configuration manager.
     * @param SqonInterface            $sqon       The Sqon manager.
     */
    public function register(
        EventDispatcherInterface $dispatcher,
        ConfigurationInterface $config,
        SqonInterface $sqon
    );
}
