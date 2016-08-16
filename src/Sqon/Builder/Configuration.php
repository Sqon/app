<?php

namespace Sqon\Builder;

use InvalidArgumentException;
use RuntimeException;
use Sqon\Builder\Exception\ConfigurationException;
use Sqon\Builder\Plugin\PluginInterface;

/**
 * Manages the build configuration settings for a Sqon builder.
 *
 * ```php
 * [
 *     'sqon' => [
 *         'bootstrap' => 'path/to/script.php',
 *         'compression' => 'GZIP',
 *         'main' => 'path/to/main.php',
 *         'output' => 'example.sqon',
 *         'paths' => [
 *             'path/to/a',
 *             'path/to/b',
 *             'path/to/c'
 *         ],
 *         'plugins' => [
 *             [
 *                 'autoload' => [
 *                     'classmap' => ['src/', 'lib/'],
 *                     'files' => ['/path/to/a.php', '/path/to/b.php'],
 *                     'psr0' => ['Example\\' => 'src/'],
 *                     'psr4' => ['Example\\' => 'src/Example']
 *                 ],
 *                 'class' => 'My\Example\Plugin'
 *             ]
 *         ],
 *         'shebang' => '#!/usr/bin/env php'
 *     ]
 * ]
 * ```
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * The default build configuration settings.
     *
     * @var array
     */
    private static $default = [
        'bootstrap' => null,
        'compression' => 'NONE',
        'main' => null,
        'output' => 'project.sqon',
        'paths' => [],
        'plugins' => [],
        'shebang' => null
    ];

    /**
     * The base directory path.
     *
     * @var string
     */
    private $directory;

    /**
     * The build configuration settings.
     *
     * @var array
     */
    private $settings;

    /**
     * Initializes the new build configuration manager.
     *
     * @param string $directory The base directory path.
     * @param array  $settings  The build configuration settings.
     */
    public function __construct($directory, array $settings)
    {
        $this->directory = $directory;
        $this->settings = $this->setDefaults($settings);
    }

    /**
     * {@inheritdoc}
     */
    public function getBootstrap()
    {
        if (null === $this->settings['sqon']['bootstrap']) {
            return null;
        }

        $contents = file_get_contents($this->settings['sqon']['bootstrap']);

        if (false === $contents) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException(
                sprintf(
                    'The PHP bootstrap script "%s" could not be read.',
                    $this->settings['sqon']['bootstrap']
                )
            );
            // @codeCoverageIgnoreEnd
        }

        return $contents;
    }

    /**
     * {@inheritdoc}
     */
    public function getCompression()
    {
        return $this->settings['sqon']['compression'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * {@inheritdoc}
     */
    public function getMain()
    {
        return $this->settings['sqon']['main'];
    }

    /**
     * {@inheritdoc}
     */
    public function getOutput()
    {
        return $this->settings['sqon']['output'];
    }

    /**
     * {@inheritdoc}
     */
    public function getPaths()
    {
        return $this->settings['sqon']['paths'];
    }

    /**
     * {@inheritdoc}
     */
    public function getPlugins()
    {
        foreach ($this->settings['sqon']['plugins'] as $plugin) {
            yield $this->getPlugin($plugin);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getShebang()
    {
        return $this->settings['sqon']['shebang'];
    }

    /**
     * {@inheritdoc}
     */
    public function getSettings($namespace)
    {
        if (isset($this->settings[$namespace])) {
            return $this->settings[$namespace];
        }

        return null;
    }

    /**
     * Returns an instance of a plugin for its information.
     *
     * @param array $plugin The plugin information.
     *
     * @return PluginInterface The plugin instance.
     *
     * @throws ConfigurationException If the plugin instance could not be created.
     */
    private function getPlugin(array $plugin)
    {
        if (isset($plugin['autoload'])) {
            $loader = get_composer_autoloader();

            if (isset($plugin['autoload']['classmap'])) {
                $loader->addClassMap($plugin['autoload']['classmap']);
            }

            if (isset($plugin['autoload']['files'])) {
                foreach ($plugin['autoload']['files'] as $file) {
                    require_once $file;
                }
            }

            if (isset($plugin['autoload']['psr0'])) {
                foreach ($plugin['autoload']['psr0'] as $prefix => $paths) {
                    $loader->add($prefix, $paths);
                }
            }

            if (isset($plugin['autoload']['psr4'])) {
                foreach ($plugin['autoload']['psr4'] as $prefix => $paths) {
                    $loader->addPsr4($prefix, $paths);
                }
            }
        }

        if (!isset($plugin['class'])) {
            // @codeCoverageIgnoreStart
            throw new ConfigurationException(
                'The plugin class is not defined.'
            );
            // @codeCoverageIgnoreEnd
        }

        if (!class_exists($plugin['class'])) {
            // @codeCoverageIgnoreStart
            throw new ConfigurationException(
                sprintf(
                    'The plugin class "%s" does not exist.',
                    $plugin['class']
                )
            );
            // @codeCoverageIgnoreEnd
        }

        if (!is_a($plugin['class'], PluginInterface::class, true)) {
            // @codeCoverageIgnoreStart
            throw new ConfigurationException(
                sprintf(
                    'The plugin class "%s" does not implement "%s".',
                    $plugin['class'],
                    PluginInterface::class
                )
            );
            // @codeCoverageIgnoreEnd
        }

        return new $plugin['class']();
    }

    /**
     * Sets the default Sqon build settings.
     *
     * This method will set any default setting that is missing from the user
     * provided configuration settings. Settings that are references to items
     * such as class constants will also be resolved.
     *
     * @param array $settings The build configuration settings.
     *
     * @return array The build configuration settings.
     *
     * @throws InvalidArgumentException If a setting is invalid.
     */
    private function setDefaults(array $settings)
    {
        if (isset($settings['sqon'])) {
            $settings['sqon'] = array_merge(self::$default, $settings['sqon']);
        } else {
            $settings['sqon'] = self::$default;
        }

        $constant = '\Sqon\Sqon::' . $settings['sqon']['compression'];

        if (!defined($constant)) {
            // @codeCoverageIgnoreStart
            throw new InvalidArgumentException(
                sprintf(
                    'The compression mode "%s" is not valid.',
                    $settings['sqon']['compression']
                )
            );
            // @codeCoverageIgnoreEnd
        }

        $settings['sqon']['compression'] = constant($constant);

        return $settings;
    }
}
