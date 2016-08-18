<?php

namespace Sqon\Builder;

use RuntimeException;
use Sqon\Builder\Exception\ConfigurationException;
use Sqon\Builder\Plugin\PluginInterface;
use Sqon\SqonInterface;
use Symfony\Component\Config\Definition\ConfigurationInterface as SchemaInterface;
use Symfony\Component\Config\Definition\Processor;

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
     * The base directory path.
     *
     * @var string
     */
    private $directory;

    /**
     * The available plugins.
     *
     * @var PluginInterface[]
     */
    private $plugins = [];

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

        $this->processSettings($settings);
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
        return $this->plugins;
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
     * {@inheritdoc}
     */
    public function getShebang()
    {
        return $this->settings['sqon']['shebang'];
    }

    /**
     * {@inheritdoc}
     */
    public function setSettings($namespace, $settings)
    {
        $this->settings[$namespace] = $settings;
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
                foreach ($plugin['autoload']['psr0'] as $psr0) {
                    $loader->add($psr0['prefix'], $psr0['paths']);
                }
            }

            if (isset($plugin['autoload']['psr4'])) {
                foreach ($plugin['autoload']['psr4'] as $psr4) {
                    $loader->addPsr4($psr4['prefix'], $psr4['paths']);
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
     * Processes the settings and loads the plugins.
     *
     * @param array $settings The current settings.
     */
    private function processSettings(array $settings)
    {
        $processor = new Processor();

        if (!isset($settings['sqon'])) {
            $settings['sqon'] = [];
        }

        $settings['sqon'] = $processor->processConfiguration(
            new Schema(),
            [$settings['sqon']]
        );

        $settings['sqon']['compression'] = constant(
            sprintf(
                '%s::%s',
                SqonInterface::class,
                $settings['sqon']['compression']
            )
        );

        foreach ($settings['sqon']['plugins'] as $plugin) {
            $plugin = $this->getPlugin($plugin);

            if ($plugin instanceof SchemaInterface) {
                $tree = $plugin->getConfigTreeBuilder()->buildTree();
                $name = $tree->getName();

                if (isset($settings[$name])) {
                    $settings[$name] = $processor->process(
                        $tree,
                        [$settings[$name]]
                    );
                }
            }

            $this->plugins[] = $plugin;
        }

        $this->settings = $settings;
    }
}
