<?php

namespace Test\Sqon\Builder;

use PHPUnit_Framework_TestCase as TestCase;
use Sqon\Builder\Configuration;
use Sqon\SqonInterface;
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
     * Verify that the plugin paths are returned.
     */
    public function testRetrieveThePathsToThePlugins()
    {
        self::assertSame(
            [],
            $this->config->getPlugins(),
            'There should be no plugins defined by default.'
        );

        $plugins = ['a', 'b', 'c'];
        $config = new Configuration(
            $this->dir,
            [
                'sqon' => [
                    'plugins' => $plugins
                ]
            ]
        );

        self::assertEquals(
            $plugins,
            $config->getPlugins(),
            'The plugin paths were not returned.'
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
