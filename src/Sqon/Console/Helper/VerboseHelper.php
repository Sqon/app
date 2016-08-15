<?php

namespace Sqon\Console\Helper;

use RuntimeException;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Manages output messages depending on verbosity level.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class VerboseHelper extends Helper
{
    /**
     * The verbosity levels.
     *
     * @var integer[]
     */
    private static $levels = [
        OutputInterface::VERBOSITY_NORMAL,
        OutputInterface::VERBOSITY_VERBOSE,
        OutputInterface::VERBOSITY_VERY_VERBOSE,
        OutputInterface::VERBOSITY_DEBUG
    ];

    /**
     * The output manager.
     *
     * @var OutputInterface
     */
    private $output;

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getName()
    {
        return 'verbose';
    }

    /**
     * Writes a message depending on the current verbosity level.
     *
     * @param array $messages The messages to choose from.
     */
    public function level(array $messages)
    {
        $messages = array_values($messages);

        for ($i = 0; $i < 4; $i++) {
            if (!isset($messages[$i])) {
                $messages[$i] = $messages[$i - 1];
            }
        }

        $messages = array_combine(self::$levels, $messages);
        $output = $this->getOutput();
        $level = $output->getVerbosity();

        if (null !== $messages[$level]) {
            $output->write(...$messages[$level]);
        }
    }

    /**
     * Sets the output manager.
     *
     * @param OutputInterface $output The output manager.
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Writes a verbose message.
     *
     * @param string  $message The message to write.
     * @param boolean $newline Write a newline?
     */
    public function v($message, $newline = false)
    {
        $output = $this->getOutput();

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->write($message, $newline);
        }
    }

    /**
     * Writes a verbose message with a newline.
     *
     * @param string $message The message to write.
     */
    public function vln($message)
    {
        $this->v($message, true);
    }

    /**
     * Writes a very verbose message.
     *
     * @param string  $message The message to write.
     * @param boolean $newline Write a newline?
     */
    public function vv($message, $newline = false)
    {
        $output = $this->getOutput();

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $output->write($message, $newline);
        }
    }

    /**
     * Writes a very verbose message with a newline.
     *
     * @param string $message The message to write.
     */
    public function vvln($message)
    {
        $this->vv($message, true);
    }

    /**
     * Writes a debugging message.
     *
     * @param string  $message The debugging message.
     * @param boolean $newline Write a newline?
     */
    public function vvv($message, $newline = false)
    {
        $output = $this->getOutput();

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
            $output->write($message, $newline);
        }
    }

    /**
     * Writes a debugging message with a newline.
     *
     * @param string $message The message to write.
     */
    public function vvvln($message)
    {
        $this->vvv($message, true);
    }

    /**
     * Returns the output manager.
     *
     * @return OutputInterface The output manager.
     *
     * @throws RuntimeException If the output manager is not set.
     */
    private function getOutput()
    {
        if (null === $this->output) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('The output manager is not set.');
            // @codeCoverageIgnoreEnd
        }

        return $this->output;
    }
}
