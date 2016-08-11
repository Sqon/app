<?php

namespace Test\Sqon\Builder\Exception;

use Exception;
use PHPUnit_Framework_TestCase as TestCase;
use Sqon\Builder\Exception\BuilderException;

/**
 * Verifies that the builder exception functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \Sqon\Builder\Exception\BuilderException
 */
class BuilderExceptionTest extends TestCase
{
    /**
     * Verify that an exception without arguments can be created.
     */
    public function testCreateExceptionWithoutAnyArguments()
    {
        new BuilderException();
    }

    /**
     * Verify that a builder exception is properly created.
     */
    public function testCreateExceptionWithArguments()
    {
        $message = 'test';
        $previous = new Exception();
        $exception = new BuilderException($message, $previous);

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
