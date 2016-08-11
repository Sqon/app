<?php

namespace Sqon\Builder\Exception;

use Exception;

/**
 * An exception thrown for an error with a Sqon builder.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class BuilderException extends Exception
{
    /**
     * Initializes the new exception.
     *
     * @param string    $message  The exception message.
     * @param Exception $previous The previous exception.
     */
    public function __construct($message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
