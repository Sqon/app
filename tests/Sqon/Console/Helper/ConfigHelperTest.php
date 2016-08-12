<?php

namespace Test\Sqon\Console\Helper;

use PHPUnit_Framework_TestCase as TestCase;
use Sqon\Console\Helper\ConfigHelper;
use Test\Sqon\Test\TempTrait;

/**
 * Verifies that the configuration helper functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \Sqon\Console\Helper\ConfigHelper
 */
class ConfigHelperTest extends TestCase
{
    use TempTrait;

    /**
     * The temporary directory.
     *
     * @var string
     */
    private $dir;

    /**
     * The configuration helper.
     *
     * @var ConfigHelper
     */
    private $helper;

    /**
     * The current working directory.
     *
     * @var string
     */
    private $pwd;

    /**
     * Verify that the configuration file can be discovered.
     */
    public function testDiscoverTheConfigurationFile()
    {
        touch($this->dir . '/sqon.yml');

        $config = $this->helper->loadConfig(null);

        self::assertEquals(
            $this->dir,
            $config->getDirectory(),
            'The directory path for the configuration file was not returned.'
        );
    }

    /**
     * Verify that the distribution configuration file can be discovered.
     */
    public function testDiscoverTheDistributionConfigurationFile()
    {
        touch($this->dir . '/sqon.yml.dist');

        $config = $this->helper->loadConfig(null);

        self::assertEquals(
            $this->dir,
            $config->getDirectory(),
            'The directory path for the configuration file was not returned.'
        );
    }

    /**
     * Verify that the configuration file can be specified.
     */
    public function testSpecifyTheConfigurationFile()
    {
        touch($this->dir . '/a/test.yml');

        $config = $this->helper->loadConfig('../test.yml');

        self::assertEquals(
            $this->dir . '/a',
            $config->getDirectory(),
            'The directory path for the configuration file was not returned.'
        );
    }

    /**
     * Creates a new configuration helper.
     */
    protected function setUp()
    {
        $this->dir = $this->createTempDirectory();
        $this->pwd = getcwd();

        mkdir($this->dir . '/a/b', 0755, true);
        chdir($this->dir . '/a/b');

        $this->helper = new ConfigHelper();
    }

    /**
     * Deletes the temporary files.
     */
    protected function tearDown()
    {
        chdir($this->pwd);

        $this->deleteTempPaths();
    }
}
