<?php

namespace Test\Sqon\Console;

use PHPUnit_Framework_TestCase as TestCase;
use Sqon\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * Verifies that the console application functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \Sqon\Console\Application
 */
class ApplicationTest extends TestCase
{
    /**
     * The application.
     *
     * @var Application
     */
    private $application;

    /**
     * The stream output manager.
     *
     * @var StreamOutput
     */
    private $output;

    /**
     * The output stream.
     *
     * @var resource
     */
    private $stream;

    /**
     * Verify that the application name and version information is set.
     */
    public function testApplicationNameAndVersionIsSet()
    {
        $this->application->run(
            new ArrayInput(['--version' => true]),
            $this->output
        );

        fseek($this->stream, 0);

        self::assertRegExp(
            '/Sqon \(repo\)/',
            trim(fgets($this->stream)),
            'The application name and version is not set properly.'
        );
    }

    /**
     * Creates a new application.
     */
    protected function setUp()
    {
        $this->stream = fopen('php://memory', 'r+');
        $this->output = new StreamOutput($this->stream);
        $this->application = new Application($this->output);

        $this->application->setAutoExit(false);
    }
}
