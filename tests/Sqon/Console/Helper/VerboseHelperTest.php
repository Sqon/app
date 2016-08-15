<?php

namespace Test\Sqon\Console\Helper;

use PHPUnit_Framework_TestCase as TestCase;
use Sqon\Console\Helper\VerboseHelper;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * Verifies that the verbose output helper functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \Sqon\Console\Helper\VerboseHelper
 */
class VerboseHelperTest extends TestCase
{
    /**
     * The helper.
     *
     * @var VerboseHelper
     */
    private $helper;

    /**
     * The output manager.
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
     * Verify that the correct message is written.
     */
    public function testWriteCorrectVerbosityLevelMessage()
    {
        $this->output->setVerbosity(StreamOutput::VERBOSITY_DEBUG);

        $this->helper->level(
            [
                ['This is a normal level message.', true],
                ['This is a verbose level message.', true],
                ['This is a very verbose level message.', true]
            ]
        );

        fseek($this->stream, 0);

        self::assertEquals(
            'This is a very verbose level message.' . PHP_EOL,
            fgets($this->stream),
            'The correct message was not written.'
        );
    }

    /**
     * Verify that a verbose message is written.
     */
    public function testWriteAVerboseMessage()
    {
        $this->output->setVerbosity(StreamOutput::VERBOSITY_VERBOSE);

        $this->helper->v('test');

        fseek($this->stream, 0);

        self::assertEquals(
            'test',
            fgets($this->stream),
            'The message was not written.'
        );
    }

    /**
     * Verify that a verbose message with a newline is written.
     */
    public function testWriteAVerboseMessageWithANewline()
    {
        $this->output->setVerbosity(StreamOutput::VERBOSITY_VERBOSE);

        $this->helper->vln('test');

        fseek($this->stream, 0);

        self::assertEquals(
            'test' . PHP_EOL,
            fgets($this->stream),
            'The message was not written.'
        );
    }

    /**
     * Verify that a very verbose message is written.
     */
    public function testWriteAVeryVerboseMessage()
    {
        $this->output->setVerbosity(StreamOutput::VERBOSITY_VERY_VERBOSE);

        $this->helper->vv('test');

        fseek($this->stream, 0);

        self::assertEquals(
            'test',
            fgets($this->stream),
            'The message was not written.'
        );
    }

    /**
     * Verify that a verbose message with a newline is written.
     */
    public function testWriteAVeryVerboseMessageWithANewline()
    {
        $this->output->setVerbosity(StreamOutput::VERBOSITY_VERY_VERBOSE);

        $this->helper->vvln('test');

        fseek($this->stream, 0);

        self::assertEquals(
            'test' . PHP_EOL,
            fgets($this->stream),
            'The message was not written.'
        );
    }

    /**
     * Verify that a debug message is written.
     */
    public function testWriteADebugMessage()
    {
        $this->output->setVerbosity(StreamOutput::VERBOSITY_DEBUG);

        $this->helper->vvv('test');

        fseek($this->stream, 0);

        self::assertEquals(
            'test',
            fgets($this->stream),
            'The message was not written.'
        );
    }

    /**
     * Verify that a debug message with a newline is written.
     */
    public function testWriteADebugMessageWithANewline()
    {
        $this->output->setVerbosity(StreamOutput::VERBOSITY_DEBUG);

        $this->helper->vvvln('test');

        fseek($this->stream, 0);

        self::assertEquals(
            'test' . PHP_EOL,
            fgets($this->stream),
            'The message was not written.'
        );
    }

    /**
     * Creates a new instance of the verbose helper.
     */
    protected function setUp()
    {
        $this->stream = fopen('php://memory', 'w+');
        $this->output = new StreamOutput($this->stream);
        $this->helper = new VerboseHelper();

        $this->helper->setOutput($this->output);
    }
}
