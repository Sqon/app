<?php

namespace Sqon\Console\Command;

use Sqon\Sqon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Manages the Sqon verification process.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class VerifyCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('verify');
        $this->setDescription('Verifies that a signature is valid');
        $this->setHelp(
            <<<HELP
The %command.name% will verify that the signature for the Sqon is valid.
HELP
        );

        $this->addArgument(
            'sqon',
            InputArgument::REQUIRED,
            'The path to the Sqon'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!file_exists($input->getArgument('sqon'))) {
            // @codeCoverageIgnoreStart
            $output->writeln('The Sqon does not exist.');

            return 3;
            // @codeCoverageIgnoreEnd
        }

        if (Sqon::isValid($input->getArgument('sqon'))) {
            $output->writeln('<fg=green>The Sqon passed verification.</fg=green>');

            return 0;
        }

        $output->writeln('<fg=red>The Sqon failed verification.</fg=red>');

        return 1;
    }
}
