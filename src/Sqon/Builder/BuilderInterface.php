<?php

namespace Sqon\Builder;

use Sqon\Builder\Exception\BuilderException;
use Sqon\SqonInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Manages the build Sqon build process using a build configuration manager.
 *
 * A Sqon build manager is used to create new or modify existing Sqons using
 * a build configuration managed by `ConfigurationInterface`. The manager is
 * also responsible for registering an event dispatcher and loading plugins
 * used to modify the build process.
 *
 * ```php
 * $builder = Builder::create($config);
 * $builder->build();
 * ```
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
interface BuilderInterface
{
    /**
     * Commits the changes made to the Sqon.
     */
    public function commit();

    /**
     * Creates a new a builder for a new Sqon.
     *
     * If a Sqon already exists in the output defined by the configuration
     * manager, it will be automatically deleted. If the new Sqon is not
     * committed, nothing will exist at the output path.
     *
     * ```php
     * $builder = Builder::create($config);
     * ```
     *
     * The compression mode is set when the builder is created.
     *
     * @param ConfigurationInterface $config The build configuration manager.
     *
     * @return BuilderInterface The builder for the new Sqon.
     *
     * @throws BuilderException If the existing file could not be deleted.
     */
    public static function create(ConfigurationInterface $config);

    /**
     * Returns the build configuration manager.
     *
     * @return ConfigurationInterface The build configuration manager.
     */
    public function getConfiguration();

    /**
     * Returns the event dispatcher for the Sqon manager.
     *
     * @return EventDispatcherInterface The event dispatcher.
     */
    public function getEventDispatcher();

    /**
     * Returns the Sqon manager.
     *
     * @return SqonInterface The Sqon manager.
     */
    public function getSqon();

    /**
     * Creates a new builder for an existing Sqon.
     *
     * The builder will modify the existing Sqon according to the settings
     * managed by the build configuration manager. All of the same processes
     * used to create a new Sqon can be selectively applied to an existing
     * Sqon.
     *
     * ```php
     * $builder = Builder::open('/path/to/project.sqon', $config);
     * ```
     *
     * The compression mode is set when the builder is created.
     *
     * @param string                 $path   The path to the existing Sqon.
     * @param ConfigurationInterface $config The configuration manager.
     *
     * @return BuilderInterface The builder for the existing Sqon.
     */
    public static function open($path, ConfigurationInterface $config);

    /**
     * Registers all available plugins with the event dispatcher.
     *
     * All of the plugins paths returned by the configuration manager will be
     * loaded and registered with the event dispatcher for the Sqon. Plugins
     * that do not exist or do not return a callback will cause the builder to
     * throw an exception.
     *
     * ```php
     * $builder->registerPlugins();
     * ```
     *
     * @return BuilderInterface A fluent interface to the builder.
     *
     * @throws BuilderException If a plugin does not exist or is invalid.
     */
    public function registerPlugins();

    /**
     * Sets the PHP bootstrap script for the Sqon.
     *
     * The PHP bootstrap script returned by the configuration manager will be
     * set for the Sqon. If a script is not returned, the default script will
     * be generated using the Sqon manager. If the default script is used and
     * the configuration manager returns a shebang line, the shebang line will
     * be added to the PHP bootstrap script.
     *
     * ```php
     * $builder->setBootstrap();
     * ```
     *
     * @return BuilderInterface A fluent interface to the builder.
     */
    public function setBootstrap();

    /**
     * Sets the path to the main script in the Sqon.
     *
     * The path is to a script that is set in the Sqon that will be executed
     * whenever the Sqon is run from the command line or loaded by another
     * script. If a path is provided by the configuration manager, it will be
     * used to generate a new primary script that will load this main script.
     *
     * ```php
     * $builder->setMain();
     * ```
     *
     * @return BuilderInterface A fluent interface to the builder.
     */
    public function setMain();

    /**
     * Sets all of the paths for the Sqon.
     *
     * All of the paths returned by the configuration manager will be set for
     * the Sqon. If the path is a directory, the directory is recursively set
     * in the Sqon. If the path is a file, the contents of the file will also
     * be set in the Sqon.
     *
     * ```php
     * $builder->setPaths();
     * ```
     *
     * @return BuilderInterface A fluent interface to the builder.
     */
    public function setPaths();
}
