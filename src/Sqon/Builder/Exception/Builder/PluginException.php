<?php

namespace Sqon\Builder\Exception\Builder;

use Exception;

/**
 * An exception thrown for an error with a Sqon builder plugin.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class PluginException extends Exception
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
