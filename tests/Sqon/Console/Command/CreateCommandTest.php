<?php

namespace Test\Sqon\Console\Command;

use PHPUnit_Framework_TestCase as TestCase;
use Sqon\Console\Command\CreateCommand;
use Sqon\Console\Helper\VerboseHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Test\Sqon\Test\TempTrait;

/**
 * Verifies that the create command functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \Sqon\Console\Command\AbstractBuildCommand
 * @covers \Sqon\Console\Command\CreateCommand
 */
class CreateCommandTest extends TestCase
{
    use TempTrait;

    /**
     * The current working directory.
     *
     * @var string
     */
    private $cwd;

    /**
     * The temporary directory.
     *
     * @var string
     */
    private $dir;

    /**
     * The command tester.
     *
     * @var CommandTester
     */
    private $tester;

    /**
     * Verify that a new Sqon can be created using a build configuration file.
     */
    public function testCreateANewSqonUsingABuildConfigurationFile()
    {
        $this->createSqon();

        self::assertEquals(
            'Creating a new Sqon... done.',
            trim($this->tester->getDisplay(true)),
            'The expected output was not written.'
        );
    }

    /**
     * Verify that a new Sqon is created with a progress bar.
     */
    public function testCreateANewSqonAndDisplayAProgressBar()
    {
        $this->createSqon(OutputInterface::VERBOSITY_VERBOSE);

        self::assertEquals(
            <<<OUTPUT
Creating a new Sqon...

Done.
OUTPUT
,
            trim($this->tester->getDisplay(true)),
            'The expected output was not written.'
        );
    }

    /**
     * Verify that a new Sqon is created with a list of paths.
     */
    public function testCreateANewSqonAndDisplayAListOfPaths()
    {
        $this->createSqon(OutputInterface::VERBOSITY_DEBUG);

        self::assertEquals(
            <<<OUTPUT
Creating a new Sqon...
 ? Bootstrap Script: default
 ? Main Script: bin/test
 ? Compression Mode: gzip
 ? Paths to set:
   - bin/test
   - lib/test.php (as: lib/test.php)
 * Paths being set:
   + bin/test
   + lib/test.php
Done.
OUTPUT
,
            trim($this->tester->getDisplay(true)),
            'The expected output was not written.'
        );
    }

    /**
     * Creates a new command tester.
     */
    protected function setUp()
    {
        $this->cwd = getcwd();
        $this->dir = $this->createTempDirectory();

        chdir($this->dir);

        $set = new HelperSet();
        $set->set(new VerboseHelper());

        $command = new CreateCommand();
        $command->setHelperSet($set);

        $this->tester = new CommandTester($command);
    }

    /**
     * Deletes the temporary paths.
     */
    protected function tearDown()
    {
        chdir($this->cwd);

        $this->deleteTempPaths();
    }

    /**
     * Runs the command to create the sqon.
     *
     * @param integer $verbosity The verbosity level.
     *
     * @return string The directory path.
     */
    private function createSqon($verbosity = OutputInterface::VERBOSITY_NORMAL)
    {
        $config = $this->dir . '/sqon.yml';

        mkdir($this->dir . '/bin');
        mkdir($this->dir . '/lib');

        file_put_contents(
            $this->dir . '/bin/test',
            '<?php require __DIR__ . "/../lib/test.php";'
        );

        file_put_contents(
            $this->dir . '/lib/test.php',
            '<?php echo "Hello, world!\n";'
        );

        static $rotate = 0;

        file_put_contents(
            $config . ((0 === ++$rotate % 2) ? '.dist' : ''),
            <<<YAML
sqon:
    compression: GZIP
    main: bin/test
    output: test.sqon
    paths:
        0: bin/test
        lib/test.php: lib/test.php
    shebang: '#!/usr/bin/env php'
YAML
        );

        $this->tester->execute(
            ['--config' => basename($config)],
            ['verbosity' => $verbosity]
        );

        self::assertFileExists(
            $this->dir . '/test.sqon',
            'The Sqon was not created.'
        );

        chmod($this->dir . '/test.sqon', 0755);

        self::assertEquals(
            'Hello, world!',
            exec($this->dir . '/test.sqon'),
            'The Sqon was not built correctly.'
        );
    }
}
