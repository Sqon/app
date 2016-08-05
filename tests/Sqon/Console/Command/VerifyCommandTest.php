<?php

namespace Test\Sqon\Console\Command;

use PHPUnit_Framework_TestCase as TestCase;
use Sqon\Console\Command\VerifyCommand;
use Sqon\Sqon;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Verifies that the Sqon verification command functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class VerifyCommandTest extends TestCase
{
    /**
     * The Sqon.
     *
     * @var string
     */
    private $sqon;

    /**
     * The command tester.
     *
     * @var CommandTester
     */
    private $tester;

    /**
     * Verify that a good Sqon passes verification.
     */
    public function testGoodSqonWillPassVerification()
    {
        $this->tester->execute(
            [
                'sqon' => $this->sqon
            ]
        );

        self::assertEquals(
            0,
            $this->tester->getStatusCode(),
            'The Sqon should have passed verification.'
        );

        self::assertEquals(
            'The Sqon passed verification.' . PHP_EOL,
            $this->tester->getDisplay(),
            'The pass message was not displayed.'
        );
    }

    /**
     * Verify that an bad Sqon fails verification.
     */
    public function testBadSqonWillFailVerification()
    {
        file_put_contents($this->sqon, 'x', FILE_APPEND);

        $this->tester->execute(
            [
                'sqon' => $this->sqon
            ]
        );

        self::assertEquals(
            1,
            $this->tester->getStatusCode(),
            'The Sqon should have failed verification.'
        );

        self::assertEquals(
            'The Sqon failed verification.' . PHP_EOL,
            $this->tester->getDisplay(),
            'The fail message was not displayed.'
        );
    }

    /**
     * Creates a new command tester.
     */
    protected function setUp()
    {
        $this->sqon = tempnam(sys_get_temp_dir(), 'sqon-');
        $this->tester = new CommandTester(new VerifyCommand());

        Sqon::create($this->sqon)->commit();
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
