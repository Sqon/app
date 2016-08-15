<?php

namespace Sqon\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Edits an existing Sqon, optionally using a build manager.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class EditCommand extends AbstractBuildCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('edit');
        $this->setDescription('Edits an existing Sqon');

        $this->addArgument(
            'sqon',
            InputArgument::REQUIRED,
            'Path to an existing Sqon'
        );

        $this->setHelp(
            <<<HELP
The <fg=yellow>edit</fg=yellow> command command will modify an existing Sqon.

An existing Sqon is edited by using one or more of the command line options,
optionally with the use of a build configuration file. The build configuration
file can be used to load plugins to modify the editing process for the Sqon.
HELP
            . $this->getHelp()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->openSqon($input->getArgument('sqon'));

        if ($input->hasParameterOption('--bootstrap')) {
            $this->setBootstrap();
        }

        if ($input->hasParameterOption('--main')) {
            $this->setMain();
        }

        if ($input->hasParameterOption('--compression')) {
            $this->setCompression();
        }

        if ($input->hasParameterOption('--path')) {
            $this->setPaths();
        }

        $this->commit();
    }
}
