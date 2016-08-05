<?php

namespace Sqon\Console\Command;

use RuntimeException;
use Sqon\Path\PathInterface;
use Sqon\Sqon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UnexpectedValueException;

/**
 * Manages the Sqon extraction process.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class ExtractCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('extract');
        $this->setDescription('Extracts the contents');
        $this->setHelp(
            <<<HELP
The %command.name% will extract one or more paths from the Sqon.

If a list of desired paths is not specified, all paths in the Sqon will be
extracted. If a directory path is not specified to extract the paths to, a
directory path will be created by using the path to the Sqon with "-contents"
appended.
HELP
        );

        $this->addOption(
            'path',
            'p',
            InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
            'The path in the Sqon to extract'
        );

        $this->addOption(
            'no-overwrite',
            null,
            InputOption::VALUE_NONE,
            'Disable overwriting existing paths'
        );

        $this->addArgument(
            'sqon',
            InputArgument::REQUIRED,
            'The path to the Sqon'
        );

        $this->addArgument(
            'dir',
            InputArgument::OPTIONAL,
            'The directory to extract to'
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

        $dir = $input->getArgument('sqon') . '-contents';

        if (null !== $input->getArgument('dir')) {
            $dir = $input->getArgument('dir');
        }

        $sqon = Sqon::open($input->getArgument('sqon'));

        ProgressBar::setFormatDefinition(
            'normal',
            <<<BAR

BAR
        );

        $done = $this->createProgressBar($output, count($sqon));
        $done->start();

        $paths = $input->getOption('path');
        $overwrite = !$input->getOption('no-overwrite');

        foreach ($sqon->getPaths() as $path => $manager) {
            if (!empty($paths) && !in_array($path, $paths)) {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $path;

            if (!$overwrite && file_exists($path)) {
                continue;
            }

            $this->extractPath($path, $manager);

            $done->setMessage($path);
            $done->advance();
        }

        $done->finish();
        $output->writeln('');

        return 0;
    }

    /**
     * Creates a new progress bar.
     *
     * @param OutputInterface $output The output manager.
     * @param integer         $max    The maximum value.
     *
     * @return ProgressBar The new progress bar.
     */
    private function createProgressBar(OutputInterface $output, $max)
    {
        // Set progress bar styles.
        $formatter = $output->getFormatter();
        $formatter->setStyle('b', new OutputFormatterStyle('blue'));
        $formatter->setStyle('c', new OutputFormatterStyle('green'));
        $formatter->setStyle('m', new OutputFormatterStyle('magenta'));
        $formatter->setStyle('p', new OutputFormatterStyle('yellow'));
        $formatter->setStyle('r', new OutputFormatterStyle('cyan'));
        $formatter->setStyle('t', new OutputFormatterStyle('red'));

        // Set default formatting styles.
        ProgressBar::setFormatDefinition(
            'normal',
            '<p>%percent:3s%%</p> <b>[</b><r>%bar%</r><b>]</b> <c>%current%</c> / <t>%max%</t>'
        );

        ProgressBar::setFormatDefinition(
            'verbose',
            '<p>%percent:3s%%</p> <b>[</b><r>%bar%</r><b>]</b> <c>%current%</c> / <t>%max%</t> - <c>%elapsed:6s%</c> / <t>%estimated:-6s%</t>'
        );

        ProgressBar::setFormatDefinition(
            'very_verbose',
            '<p>%percent:3s%%</p> <b>[</b><r>%bar%</r><b>]</b> <c>%current%</c> / <t>%max%</t> - <c>%elapsed:6s%</c> / <t>%estimated:-6s%</t> - <m>%memory:6s%</m>'
        );

        ProgressBar::setFormatDefinition(
            'debug',
            "<p>%percent:3s%%</p> <b>[</b><r>%bar%</r><b>]</b> <c>%current%</c> / <t>%max%</t> - <c>%elapsed:6s%</c> / <t>%estimated:-6s%</t> - <m>%memory:6s%</m>\n%message%"
        );

        // Create the progress bar.
        $progress = new ProgressBar($output, $max);
        $progress->setRedrawFrequency(ceil($max * 0.01));

        return $progress;
    }

    /**
     * Extracts a path to the filesystem.
     *
     * @param string        $path    The path on the filesystem.
     * @param PathInterface $manager The path manager.
     *
     * @throws RuntimeException If the path could not be extracted.
     */
    private function extractPath($path, PathInterface $manager)
    {
        switch ($manager->getType()) {
            case PathInterface::DIRECTORY:
                if (!is_dir($path) && !mkdir($path, 0755, true)) {
                    // @codeCoverageIgnoreStart
                    throw new RuntimeException(
                        "The directory \"$path\" could not be created."
                    );
                    // @codeCoverageIgnoreEnd
                }

                break;

            case PathInterface::FILE:
                $base = dirname($path);

                if (!is_dir($base) && !mkdir($base, 0755, true)) {
                    // @codeCoverageIgnoreStart
                    throw new RuntimeException(
                        "The directory \"$base\" could not be created."
                    );
                    // @codeCoverageIgnoreEnd
                }

                if (false === file_put_contents($path, $manager->getContents())) {
                    // @codeCoverageIgnoreStart
                    throw new RuntimeException(
                        "The file \"$path\" could not be written."
                    );
                    // @codeCoverageIgnoreEnd
                }

                break;

        // @codeCoverageIgnoreStart
            default:
                throw new UnexpectedValueException(
                    sprintf(
                        'The path type (%d) for "%s" is not recognized.',
                        $manager->getType(),
                        $path
                    )
                );
        }
        // @codeCoverageIgnoreEnd

        if (!chmod($path, $manager->getPermissions())) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException(
                "The permissions for \"$path\" could not be set."
            );
            // @codeCoverageIgnoreEnd
        }

        if (!touch($path, $manager->getModified())) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException(
                "The modified timestamp for \"$path\" could not be set."
            );
            // @codeCoverageIgnoreEnd
        }
    }
}
