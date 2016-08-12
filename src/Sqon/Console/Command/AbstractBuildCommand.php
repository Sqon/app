<?php

namespace Sqon\Console\Command;

use Sqon\Builder\ConfigurationInterface;
use Sqon\Console\Helper\ConfigHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Serves as the base class for a build configurable command.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
abstract class AbstractBuildCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addOption(
            'config',
            'c',
            InputOption::VALUE_REQUIRED,
            'The path to the build configuration file',
            ConfigHelper::FILE
        );
    }

    /**
     * Returns the build configuration manager.
     *
     * @param InputInterface $input The input manager.
     *
     * @return ConfigurationInterface The build configuration manager.
     */
    protected function getConfig(InputInterface $input)
    {
        return $this
            ->getHelper('config')
            ->loadConfig($input->getOption('config'))
        ;
    }
}
