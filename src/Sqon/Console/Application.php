<?php

namespace Sqon\Console;

use Sqon\Console\Command\ExtractCommand;
use Sqon\Console\Command\VerifyCommand;
use Symfony\Component\Console\Application as Base;

/**
 * Manages the handling of command line input and output.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Application extends Base
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct('Sqon', '0.0.0');
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        return array_merge(
            parent::getDefaultCommands(),
            [
                new ExtractCommand(),
                new VerifyCommand()
            ]
        );
    }
}
