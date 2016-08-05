<?php

namespace Test\Sqon\Console\Command;

use ArrayIterator;
use PHPUnit_Framework_TestCase as TestCase;
use Sqon\Console\Command\ExtractCommand;
use Sqon\Path\Memory;
use Sqon\Path\PathInterface;
use Sqon\Sqon;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Verifies that the Sqon extract command functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class ExtractCommandTest extends TestCase
{
    /**
     * The path managers.
     *
     * @var PathInterface[]
     */
    private $paths;

    /**
     * The Sqon.
     *
     * @var string
     */
    private $sqon;

    /**
     * The temporary directory path.
     *
     * @var string
     */
    private $temp;

    /**
     * The command tester.
     *
     * @var CommandTester
     */
    private $tester;

    /**
     * Verify that all paths can be extracted.
     */
    public function testExtractAllPathsInASqon()
    {
        touch($this->temp . '/a.php');

        $this->tester->execute(
            [
                '--no-overwrite' => true,
                'sqon' => $this->sqon,
                'dir' => $this->temp
            ]
        );

        self::assertEquals(
            '',
            file_get_contents($this->temp . '/a.php'),
            'A file was overwritten.'
        );

        self::assertFileExists(
            $this->temp . '/b',
            'A directory was not extracted.'
        );

        self::assertEquals(
            'd.php',
            file_get_contents($this->temp . '/c/d.php'),
            'A file was not extracted properly.'
        );
    }

    /**
     * Verify that specific paths can be extracted.
     */
    public function testExtractSpecificPathsInASqon()
    {
        $this->tester->execute(
            [
                '--path' => ['a.php'],
                'sqon' => $this->sqon,
                'dir' => $this->temp
            ]
        );

        self::assertEquals(
            'a.php',
            file_get_contents($this->temp. '/a.php'),
            'A file was not extracted properly.'
        );

        self::assertFileNotExists(
            $this->temp . '/b',
            'The extracted paths were not filtered.'
        );

        self::assertFileNotExists(
            $this->temp . '/c.php',
            'The extracted paths were not filtered.'
        );
    }

    /**
     * Creates a new command tester.
     */
    protected function setUp()
    {
        $this->paths = [
            'a.php' => new Memory('a.php'),
            'b' => new Memory('b', Memory::DIRECTORY),
            'c/d.php' => new Memory('d.php')
        ];

        $this->sqon = tempnam(sys_get_temp_dir(), 'sqon-');
        $this->tester = new CommandTester(new ExtractCommand());
        $this->temp = tempnam(sys_get_temp_dir(), 'sqon-');

        unlink($this->temp);
        mkdir($this->temp);

        Sqon::create($this->sqon)
            ->setPathsUsingIterator(new ArrayIterator($this->paths))
            ->commit()
        ;
    }

    /**
     * Deletes the Sqon.
     */
    protected function tearDown()
    {
        if (file_exists($this->sqon)) {
            unlink($this->sqon);
        }
    }
}
