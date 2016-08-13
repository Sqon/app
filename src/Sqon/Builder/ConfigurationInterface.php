<?php

namespace Sqon\Builder;

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
     *         $absolute = $config->getDirectory() . DIRECTORY_SEPARATOR . $path;
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
     * Returns the paths to the plugins for the Sqon manager.
     *
     * A plugin is a PHP script that returns a callback that accepts an event
     * dispatcher and Sqon manager as its arguments. The callback is expected
     * to register a listener or subscriber for one or more of the events that
     * are used by the Sqon manager.
     *
     * ```php
     * use Sqon\SqonInterface;
     * use Symfony\Component\EventDispatcher\EventDispatcherInterface;
     *
     * return function (
     *     EventDispatcherInterface $dispatcher,
     *     SqonInterface $sqon
     * ) {
     *     // ...
     * }
     * ```
     *
     * ```php
     * foreach ($config->getPlugins() as $plugin) {
     *     call_user_func(
     *         include $plugin,
     *         $sqon->getEventDispatcher(),
     *         $sqon
     *     );
     * }
     * ```
     *
     * If a plugin uses an external library to perform its function, you may
     * want to register your class paths with the Composer autoloader that is
     * bundled with the application. The Composer class loader can be retrieved
     * by calling `get_composer_autoloader()`.
     *
     * > This function is only available when the application is executed.
     * > For testing, you may need to define your own version of the function
     * > that returned the class loader.
     *
     * ```php
     * use Symfony\Component\EventDispatcher\EventDispatcherInterface;
     *
     * $loader = get_composer_autoloader();
     * $loader->addPsr('My\\Library\\', __DIR__ . '/src/My/Library');
     *
     * return function (EventDispatcherInterface $dispatcher) {
     *     // ...
     * };
     * ```
     *
     * @return string[] The paths to the plugins.
     */
    public function getPlugins();

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
}
