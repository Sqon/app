<?php

namespace Sqon\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates a new Sqon using a build manager.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class CreateCommand extends AbstractBuildCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('create');
        $this->setDescription('Creates a new Sqon');

        $this
            ->getDefinition()
            ->getOption('config')
            ->setDefault('sqon.yml')
        ;

        $this->setHelp(
            <<<HELP
The <fg=yellow>create</fg=yellow> command will build a new Sqon using a build configuration file.

By default, the command will check the current working directory for a file
named "sqon.yml". If that file does not exist, then a check is performed for
"sqon.yml.dist". The first file located will be used to configure the build
process.
HELP
            . $this->getHelp()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->createSqon();
        $this->setBootstrap();
        $this->setMain();
        $this->setCompression();
        $this->setPaths();
        $this->commit();
    }
}
