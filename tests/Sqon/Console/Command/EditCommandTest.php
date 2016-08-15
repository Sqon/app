<?php

namespace Test\Sqon\Console\Command;

use PHPUnit_Framework_TestCase as TestCase;
use Sqon\Console\Command\EditCommand;
use Sqon\Console\Helper\VerboseHelper;
use Sqon\Path\Memory;
use Sqon\Path\PathInterface;
use Sqon\Sqon;
use Sqon\SqonInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Tester\CommandTester;
use Test\Sqon\Test\TempTrait;

/**
 * Verifies that the edit command functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \Sqon\Console\Command\AbstractBuildCommand
 * @covers \Sqon\Console\Command\EditCommand
 */
class EditCommandTest extends TestCase
{
    use TempTrait;

    /**
     * The real current working directory.
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
     * The path to the existing Sqon.
     *
     * @var string
     */
    private $existing;

    /**
     * The paths in the Sqon.
     *
     * @var PathInterface[]
     */
    private $paths;

    /**
     * The command tester.
     *
     * @var CommandTester
     */
    private $tester;

    /**
     * Verify that only the PHP bootstrap script is edited.
     */
    public function testEditThePhpBootstrapScript()
    {
        // Using a custom script.
        $file = $this->createTempFile();
        $script = '<?php __HALT_COMPILER();';

        file_put_contents($file, $script);

        $this->tester->execute(
            [
                '--bootstrap' => $file,
                'sqon' => $this->existing
            ]
        );

        $sqon = Sqon::open($this->existing);

        self::assertEquals(
            $script,
            $sqon->getBootstrap(),
            'The PHP bootstrap script was not changed.'
        );

        $sqon = null;

        // Using the default script.
        $this->tester->execute(
            [
                '--bootstrap' => null,
                '--shebang' => '#!/usr/bin/php',
                'sqon' => $this->existing
            ]
        );

        $sqon = Sqon::open($this->existing);

        self::assertStringStartsWith(
            '#!/usr/bin/php',
            $sqon->getBootstrap(),
            'The PHP bootstrap script was not changed.'
        );

        $this->assertPrimaryNotChanged($sqon);
        $this->assertNoPathsChanged($sqon);
    }

    /**
     * Verify that only the main script is edited.
     */
    public function testEditTheMainScript()
    {
        // Set the primary script.
        $this->tester->execute(
            [
                '--main' => 'test/main.php',
                'sqon' => $this->existing
            ]
        );

        $sqon = Sqon::open($this->existing);

        self::assertContains(
            'test/main.php',
            $sqon->getPath(Sqon::PRIMARY)->getContents(),
            'The primary script was not set.'
        );

        $sqon = null;

        // Remove the primary script.
        $this->tester->execute(
            [
                '--main' => null,
                'sqon' => $this->existing
            ]
        );

        $sqon = Sqon::open($this->existing);

        self::assertFalse(
            $sqon->hasPath(Sqon::PRIMARY),
            'The primary script was not removed.'
        );

        $this->assertBootstrapNotChanged($sqon);
        $this->assertNoPathsChanged($sqon);
    }

    /**
     * Verify that new paths are set.
     */
    public function testSetNewPaths()
    {
        // Set a new path.
        $file = $this->dir . '/new.php';

        file_put_contents($file, 'new');

        $this->tester->execute(
            [
                '--base' => dirname($file),
                '--compression' => 'GZIP',
                '--path' => [$file],
                'sqon' => $this->existing
            ]
        );

        $sqon = Sqon::open($this->existing);

        self::assertEquals(
            'new',
            $sqon->getPath('new.php')->getContents(),
            'The new path was not set.'
        );

        self::assertEquals(
            'test',
            $sqon->getPath('test.php')->getContents(),
            'The existing path was changed.'
        );

        // Change an existing path.
        $file = $this->dir . '/test.php';

        file_put_contents($file, 'changed');

        $this->tester->execute(
            [
                '--base' => dirname($file),
                '--path' => [$file],
                'sqon' => $this->existing
            ]
        );

        $sqon = Sqon::open($this->existing);

        self::assertEquals(
            'new',
            $sqon->getPath('new.php')->getContents(),
            'The previously added file was removed..'
        );

        self::assertEquals(
            'changed',
            $sqon->getPath('test.php')->getContents(),
            'The existing path was not changed.'
        );

        $this->assertBootstrapNotChanged($sqon);
        $this->assertPrimaryNotChanged($sqon);
    }

    /**
     * Creates a new command tester.
     */
    protected function setUp()
    {
        $this->cwd = getcwd();
        $this->dir = $this->createTempDirectory();
        $this->existing = $this->createTempFile();

        chdir($this->dir);

        $set = new HelperSet();
        $set->set(new VerboseHelper());

        $command = new EditCommand();
        $command->setHelperSet($set);

        $this->tester = new CommandTester($command);

        $this->createSqon();
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
     * Asserts that the PHP bootstrap script was not changed.
     *
     * @param SqonInterface $sqon The Sqon manager.
     */
    private function assertBootstrapNotChanged(SqonInterface $sqon)
    {
        self::assertEquals(
            Sqon::createBootstrap('#!/usr/bin/env php'),
            $sqon->getBootstrap(),
            'The PHP bootstrap script was changed.'
        );
    }

    /**
     * Asserts that no paths were changed.
     *
     * @param SqonInterface $sqon The Sqon manager.
     */
    private function assertNoPathsChanged(SqonInterface $sqon)
    {
        $paths = iterator_to_array($sqon->getPaths());

        unset($paths[Sqon::PRIMARY]);

        self::assertEquals(
            $this->paths,
            $paths,
            'The paths were changed.'
        );
    }

    /**
     * Asserts that the primary script was not changed.
     *
     * @param SqonInterface $sqon The Sqon manager.
     */
    private function assertPrimaryNotChanged(SqonInterface $sqon)
    {
        self::assertEquals(
            'main',
            $sqon->getPath(Sqon::PRIMARY)->getContents(),
            'The primary script was changed.'
        );
    }

    /**
     * Creates a new Sqon for editing.
     *
     * @return string The path to the Sqon.
     */
    private function createSqon()
    {
        $sqon = Sqon::create($this->existing)
            ->setBootstrap(Sqon::createBootstrap('#!/usr/bin/env php'))
            ->setPath(Sqon::PRIMARY, new Memory('main'))
            ->setPath('test.php', new Memory('test'))
        ;

        $sqon->commit();

        $this->paths = iterator_to_array($sqon->getPaths());

        unset($this->paths[Sqon::PRIMARY]);
    }
}
