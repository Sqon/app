<?php

namespace Test\Sqon\Builder;

use PHPUnit_Framework_TestCase as TestCase;
use Sqon\Builder\Configuration;
use Sqon\SqonInterface;
use Test\Sqon\Builder\Plugin\TestPlugin;
use Test\Sqon\Test\TempTrait;

/**
 * Verifies that the build configuration manager functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \Sqon\Builder\Configuration
 */
class ConfigurationTest extends TestCase
{
    use TempTrait;

    /**
     * The build configuration manager.
     *
     * @var Configuration
     */
    private $config;

    /**
     * The build directory path.
     *
     * @var string
     */
    private $dir;

    /**
     * Verify that the PHP bootstrap script is returned.
     */
    public function testRetrievePhpBootstrapScript()
    {
        self::assertNull(
            $this->config->getBootstrap(),
            'A PHP bootstrap script should not be returned by default.'
        );

        $file = $this->createTempFile();

        file_put_contents($file, 'test');

        $config = new Configuration(
            $this->dir,
            [
                'sqon' => [
                    'bootstrap' => $file
                ]
            ]
        );

        self::assertEquals(
            'test',
            $config->getBootstrap(),
            'The PHP bootstrap script was not returned.'
        );
    }

    /**
     * Verify that the compression mode is returned.
     */
    public function testRetrieveCompressionMode()
    {
        self::assertEquals(
            SqonInterface::NONE,
            $this->config->getCompression(),
            'Compression should be disabled by default.'
        );

        $config = new Configuration(
            $this->dir,
            [
                'sqon' => [
                    'compression' => 'GZIP'
                ]
            ]
        );

        self::assertEquals(
            SqonInterface::GZIP,
            $config->getCompression(),
            'The compression mode was not returned.'
        );
    }

    /**
     * Verify that the base directory path is returned.
     */
    public function testRetrieveBaseDirectoryPath()
    {
        self::assertEquals(
            $this->dir,
            $this->config->getDirectory(),
            'The base directory path was not returned.'
        );
    }

    /**
     * Verify that the path to the main script is returned.
     */
    public function testRetrievePathToTheMainScript()
    {
        self::assertNull(
            $this->config->getMain(),
            'A path to a main script should not be set by default.'
        );

        $config = new Configuration(
            $this->dir,
            [
                'sqon' => [
                    'main' => 'path/to/main.php'
                ]
            ]
        );

        self::assertEquals(
            'path/to/main.php',
            $config->getMain(),
            'The path to the main script was not returned.'
        );
    }

    /**
     * Verify that the output path is returned.
     */
    public function testRetrieveTheOutputPath()
    {
        self::assertEquals(
            'project.sqon',
            $this->config->getOutput(),
            'The default output path was not returned.'
        );

        $config = new Configuration(
            $this->dir,
            [
                'sqon' => [
                    'output' => 'test.sqon'
                ]
            ]
        );

        self::assertEquals(
            'test.sqon',
            $config->getOutput(),
            'The output path was not returned.'
        );
    }

    /**
     * Verify that namespaced settings can be retrieved.
     */
    public function testRetrieveNamespacedSettings()
    {
        $config = new Configuration($this->dir, []);

        self::assertNull(
            $config->getSettings('test'),
            'The "test" namespace should have no settings.'
        );

        $config = new Configuration(
            $this->dir,
            [
                'test' => [
                    'value' => 123
                ]
            ]
        );

        self::assertEquals(
            ['value' => 123],
            $config->getSettings('test'),
            'The namespaced settings were not returned.'
        );
    }

    /**
     * Verify that the plugins are registered with an event dispatcher.
     */
    public function testRegisterPluginsWithAnEventDispatcher()
    {
        $classmap = ['PluginTest/'];
        $files = [__DIR__ . '/../../test.php'];
        $psr0 = ['PluginTest\\' => ['src/']];
        $psr4 = ['PluginTest\\' => ['plugin/test/']];

        $config = new Configuration(
            $this->dir,
            [
                'sqon' => [
                    'plugins' => [
                        [
                            'autoload' => [
                                'classmap' => $classmap,
                                'files' => $files,
                                'psr0' => $psr0,
                                'psr4' => $psr4
                            ],
                            'class' => TestPlugin::class
                        ]
                    ]
                ]
            ]
        );

        foreach ($config->getPlugins() as $plugin) {
            self::assertInstanceOf(
                TestPlugin::class,
                $plugin,
                'The test plugin was not returned.'
            );
        }

        $loader = get_composer_autoloader();

        self::assertEquals(
            $classmap,
            $loader->getClassMap(),
            'The class map was not set.'
        );

        self::assertTrue(
            function_exists('plugin_registration_successful'),
            'The plugin file was not loaded.'
        );

        self::assertEquals(
            $psr0,
            $loader->getPrefixes(),
            'The PSR-0 prefixes were not registered.'
        );

        self::assertEquals(
            $psr4,
            $loader->getPrefixesPsr4(),
            'The PSR-4 prefixes were not registered.'
        );
    }

    /**
     * Verify that the paths to set are returned.
     */
    public function testRetrievePathsToSetInTheSqon()
    {
        self::assertSame(
            [],
            $this->config->getPaths(),
            'No paths to set should be returned by default.'
        );

        $config = new Configuration(
            $this->dir,
            [
                'sqon' => [
                    'paths' => [
                        'a/b/c',
                        'd/e/f',
                        'g/h/i'
                    ]
                ]
            ]
        );

        self::assertEquals(
            [
                'a/b/c',
                'd/e/f',
                'g/h/i'
            ],
            $config->getPaths(),
            'The paths to set were not returned.'
        );
    }

    /**
     * Verify that the shebang line is returned.
     */
    public function testRetrieveThePhpBootstrapScriptShebangLine()
    {
        self::assertNull(
            $this->config->getShebang(),
            'A shebang line should not be set by default.'
        );

        $config = new Configuration(
            $this->dir,
            [
                'sqon' => [
                    'shebang' => 'test'
                ]
            ]
        );

        self::assertEquals(
            'test',
            $config->getShebang(),
            'The shebang line was not returned.'
        );
    }

    /**
     * Verify that the namespaced settings can be set.
     */
    public function testSetNewNamespacedSettings()
    {
        $this->config->setSettings('test', 123);

        self::assertEquals(
            123,
            $this->config->getSettings('test'),
            'The new settings were not set.'
        );
    }

    /**
     * Creates a new build configuration manager.
     */
    protected function setUp()
    {
        $this->dir = '/example/path/to/build';
        $this->config = new Configuration($this->dir, []);
    }

    /**
     * Deletes the temporary paths.
     */
    protected function tearDown()
    {
        $this->deleteTempPaths();
    }
}
