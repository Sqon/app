<?php

namespace Sqon\Builder;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Defines the public interface for a Sqon build configuration manager.
 *
 * An implementation of this interface is used to manage the build attributes
 * of new and existing Sqons. While specific attributes are directly accessed
 * by their respective methods, plugins will have access to their attributes
 * stored in their respective namespaces.
 *
 * ```php
 * $config = new Configuration($settings);
 * ```
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
interface ConfigurationInterface
{
    /**
     * Returns the PHP bootstrap script for a new Sqon.
     *
     * This method returns the PHP bootstrap script that is used to create a
     * new Sqon. If a script is not returned, the default provided by the Sqon
     * manager will be used.
     *
     * ```php
     * $sqon = Sqon::create('/path/to/example.sqon', $config->getBootstrap());
     * ```
     *
     * ```php
     * $sqon->setBootstrap($config->getBootstrap());
     * ```
     *
     * @return null|string The PHP bootstrap script.
     */
    public function getBootstrap();

    /**
     * Returns the compression mode for newly added paths.
     *
     * This method will return the compression mode that will be used for the
     * contents of new paths that are set using the Sqon manager. By default,
     * the `SqonInterface::NONE` compression mode is returned if a mode has
     * not been specified.
     *
     * ```php
     * $sqon->setCompression($config->getCompression());
     * ```
     *
     * @return integer The compression mode.
     */
    public function getCompression();

    /**
     * Returns the base directory path used to resolve relative paths.
     *
     * When a relative path is returned from the configuration manager, this
     * directory path is used to convert the relative path into an absolute
     * path. If the build configuration settings were loaded from a file, the
     * directory containing the file is returned. Otherwise, the current
     * working directory is returned.
     *
     * ```php
     * $base = $config->getDirectory();
     * ```
     *
     * @return string The base directory path.
     */
    public function getDirectory();

    /**
     * Returns the path to the main script in the Sqon.
     *
     * The path returned by this method will be used to create a small script
     * that will be executed every time the Sqon is executed from the command
     * line or loaded by another script. If a path is not provided, the Sqon
     * will not do anything other than self extract. By default, no path is
     * returned.
     *
     * ```php
     * $sqon->setPath(
     *     SqonInterface::PRIMARY,
     *     sprintf(
     *         "require '%s';",
     *         $config->getMain()
     *     )
     * );
     * ```
     *
     * @return null|string The path to the script.
     */
    public function getMain();

    /**
     * Returns the path for the new Sqon.
     *
     * This path returned by this method is where a newly generated Sqon will
     * be written to. If a file already exists at the path, it is replaced by
     * the newly generated Sqon.
     *
     * By default, `project.sqon` is returned.
     *
     * ```php
     * $sqon = Sqon::create($config->getOutput());
     * ```
     *
     * @return string The path to the new Sqon.
     */
    public function getOutput();

    /**
     * Returns the paths to set in the new Sqon.
     *
     * When a new Sqon is created the paths returned by this method will be set
     * in the Sqon. If the path is for a file, the contents of the file will be
     * stored. If the path is a directory, the directory and its contents are
     * recursively set in the Sqon.
     *
     * ```php
     * use Sqon\Iterator\DirectoryIterator;
     * use Sqon\Path\File;
     *
     * foreach ($config->getPaths() as $relative => $absolute) {
     *     if (is_integer($relative)) {
     *         $relative = $absolute;
     *         $absolute = $config->getDirectory()
     *             . DIRECTORY_SEPARATOR
     *             . $path;
     *     }
     *
     *     if (is_dir($absolute)) {
     *         $sqon->setPathsUsingIterator(new DirectoryIterator($absolute));
     *     } else {
     *         $sqon->setPath($relative, new File($absolute));
     *     }
     * }
     * ```
     *
     * @return string[] The paths to set.
     */
    public function getPaths();

    /**
     * Returns the settings for a namespace.
     *
     * This method will provide a plugin access to settings that are stored in
     * a namespace. These namespaces and their values are defined by plugins
     * and can continue any value that is needed by the plugin.
     *
     * ```php
     * $settings = $config->getSettings('plugin_namespace');
     *
     * if (null !== $settings) {
     *     // ...
     * }
     * ```
     *
     * @param string $namespace The namespace for the settings.
     *
     * @return mixed|null The settings from the namespace.
     */
    public function getSettings($namespace);

    /**
     * Returns the shebang line for the PHP bootstrap script.
     *
     * The shebang line returned is used when creating the default PHP
     * bootstrap provided by the Sqon manager. If a shebang line is not
     * returned, the PHP bootstrap script will not have a shebang line.
     * By default, no shebang line is returned.
     *
     * ```php
     * $sqon->setBootstrap(Sqon::createBootstrap($config->getShebang());
     * ```
     *
     * ```php
     * $sqon = Sqon::create(
     *     $config->getOutput(),
     *     Sqon::createBootstrap($config->getShebang()
     * );
     * ```
     *
     * @return null|string The shebang line.
     */
    public function getShebang();

    /**
     * Registers the plugins with an event dispatcher.
     *
     * ```php
     * $config->registerPlugins($dispatcher);
     * ```
     *
     * A plugin is a PHP script that returns a callback that accepts an event
     * dispatcher and build configuration manager as its arguments. The callback
     * registers one or more event listeners or subscribers with the event
     * dispatcher.
     *
     * ```php
     * use Sqon\Builder\ConfigurationInterface;
     * use Symfony\Component\EventDispatcher\EventDispatcherInterface;
     *
     * return function (
     *     EventDispatcherInterface $dispatcher,
     *     ConfigurationInterface $config
     * ) {
     *     $settings = $config->getSettings('my_plugin_settings');
     *
     *     // ...
     * };
     * ```
     *
     * If the callback registers a listener or subscriber that requires an
     * external library, the `get_composer_autoloader()` function can be used
     * to register the class paths with the class loader.
     *
     * ```php
     * $loader = get_composer_autoloader();
     * $loader->addPsr4('My\\Example\\', '/path/to/src/My/Example');
     * ```
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     */
    public function registerPlugins(EventDispatcherInterface $dispatcher);
}
