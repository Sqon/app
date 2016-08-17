<?php

namespace Test\Sqon\Builder\Exception;

use Exception;
use PHPUnit_Framework_TestCase as TestCase;
use Sqon\Builder\Exception\Builder\PluginException;

/**
 * Verifies that the builder exception functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \Sqon\Builder\Exception\Builder\PluginException
 */
class PluginExceptionTest extends TestCase
{
    /**
     * Verify that an exception without arguments can be created.
     */
    public function testCreateExceptionWithoutAnyArguments()
    {
        new PluginException();
    }

    /**
     * Verify that a builder exception is properly created.
     */
    public function testCreateExceptionWithArguments()
    {
        $message = 'test';
        $previous = new Exception();
        $exception = new PluginException($message, $previous);

        self::assertEquals(
            $message,
            $exception->getMessage(),
            'The exception message was not set.'
        );

        self::assertSame(
            $previous,
            $exception->getPrevious(),
            'The previous exception was not set.'
        );
    }
}
