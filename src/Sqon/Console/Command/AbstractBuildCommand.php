<?php

namespace Sqon\Console\Command;

use InvalidArgumentException;
use KHerGe\File\File;
use Sqon\Builder\Builder;
use Sqon\Builder\Configuration;
use Sqon\Builder\Event\ProgressSubscriber;
use Sqon\Builder\Event\ReportSubscriber;
use Sqon\Console\Helper\VerboseHelper;
use Sqon\SqonInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

use function Sqon\canonicalize;
use function Sqon\is_relative;

/**
 * Provides the base build command fron creating and editing Sqons.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class AbstractBuildCommand extends Command
{
    /**
     * The build manager.
     *
     * @var Builder
     */
    private $builder;

    /**
     * The number of changes performed.
     *
     * @var integer
     */
    private $changes = 0;

    /**
     * The recognized compression modes.
     *
     * @var string[]
     */
    private static $compression = [
        SqonInterface::BZIP2 => '<fg=yellow>bzip2</fg=yellow>',
        SqonInterface::GZIP => '<fg=yellow>gzip</fg=yellow>',
        SqonInterface::NONE => '<fg=magenta>none</fg=magenta>'
    ];

    /**
     * The input manager.
     *
     * @var InputInterface
     */
    private $input;

    /**
     * The output manager.
     *
     * @var OutputInterface
     */
    private $output;

    /**
     * The verbose helper.
     *
     * @var VerboseHelper
     */
    private $verbose;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addOption(
            'base',
            null,
            InputOption::VALUE_OPTIONAL,
            'Directory path used to resolve relative paths'
        );

        $this->addOption(
            'bootstrap',
            null,
            InputOption::VALUE_OPTIONAL,
            'Path to the PHP bootstrap script'
        );

        $this->addOption(
            'compression',
            null,
            InputOption::VALUE_REQUIRED,
            'Compression mode for new paths set'
        );

        $this->addOption(
            'config',
            null,
            InputOption::VALUE_REQUIRED,
            'Path to the build configuration file'
        );

        $this->addOption(
            'main',
            null,
            InputOption::VALUE_OPTIONAL,
            'Path to the main script'
        );

        $this->addOption(
            'no-plugins',
            null,
            InputOption::VALUE_NONE,
            'Disables the use of builder plugins'
        );

        $this->addOption(
            'path',
            null,
            InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
            'Path to set in the Sqon'
        );

        $this->addOption(
            'shebang',
            null,
            InputOption::VALUE_OPTIONAL,
            'Shebang line for the PHP bootstrap script'
        );

        $this->setHelp(
            <<<HELP


<fg=cyan>Build Configuration</fg=cyan>

The build configuration file is used to define the build configuration settings
as well as settings for plugins. Some of these settings can be overridden using
one or more of the command line options listed below. The following is an
example of what a build configuration file could look like.

<fg=red># Make the new Sqon executable.
chmod: 0755

# Filter out some files.
filter:

    # List paths to exclude.
    exclude:

        # Exclude paths by regular expression.
        regex:
            - '/[Tt]ests/'

    # List paths to always include.
    include:

        # Include paths by name.
        name:
            - 'Apache 2.0.txt'
            - 'LICENSE'
            - 'MIT.txt'

        # Include paths by regular expression.
        regex:
            - '/\.php$/'

# Set the build configuration.
sqon:

    # Compress contents using gzip.
    compression: GZIP

    # Use the executable script as main.
    main: 'bin/sqon'

    # Registers Sqon manager plugins.
    plugins:
    
        # Loads the built-in Chmod plugin.
        - class: Sqon\Builder\Plugin\Chmod
        
        # Load the built-in Filter plugin.
        - class: Sqon\Builder\Plugin\Filter

    # Create the Sqon as "sqon".
    output: 'sqon'

    # Include all paths at the project root.
    paths: ['.']

    # Include a shebang line with the PHP bootstrap script.
    shebang: '#!/usr/bin/env php'
</fg=red>

<fg=cyan>Options</fg=cyan>

<fg=yellow>It is important to understand that using these options will override their
respective setting in the build configuration file (if one is used). If an
option is not provided, the setting from the build configuration file will
be used unless an existing Sqon is being modified. If an option is provided
but no value is given, the default value from the Sqon manager is used.</fg=yellow>

The <fg=green>base</fg=green> option is the path to the directory that is used to resolve relative
paths. In addition to resolve relative paths, it is also used to determine what
part of an absolute path should be removed in order to convert it into a
relative path inside the Sqon.

The <fg=green>bootstrap</fg=green> option is the path to a file that will be used as the PHP
bootstrap script for the Sqon. The PHP bootstrap script must always load the
primary script (if one is set) and end with "__HALT_COMPILER();". Since this
is a custom bootstrap script, the <fg=green>shebang</fg=green> option will not work and you will
need to include your own shebang line if one is desired. If the path is not
absolute, it will be relative to the current working directory.

The <fg=green>compression</fg=green> option sets the compression mode for new paths that are set in
the Sqon. The compression mode is not changed for any file that is already set
in the Sqon.

The <fg=green>config</fg=green> option is the path to the build configuration file. If the path is
not absolute, it will be relative to the current working directory. If the file
does not exist, ".dist" is appended to the path and another attempt is made to
load it.

The <fg=green>no-plugins</fg=green> option will disable the registration of any plugins defined in
the build configuration file. This is really only useful if debugging an issue
with building Sqons.

The <fg=green>main</fg=green> option is the path to a script inside the Sqon to run with the Sqon is
executed from the command line or loaded by another PHP script. If the PHP
bootstrap script is custom and does not properly load the primary script, this
option will have no effect. If the path is not absolute, the path will be
relative to the root of the Sqon directory.

The <fg=green>path</fg=green> option is a path on the file system to recursively set in the Sqon.
This option can be provided to set multiple paths in the Sqon. If a path already
exists in the Sqon, it will be replaced. Existing paths will not be deleted from
the Sqon.

The <fg=green>shebang</fg=green> option sets the shebang line for the PHP bootstrap script as it is
generated by the Sqon manager. If a custom bootstrap script is provided using
the <fg=green>bootstrap</fg=green> option, this option will have no effect.
HELP
        );
    }

    /**
     * Commits the changes made to the Sqon.
     */
    protected function commit()
    {
        if (0 < $this->changes) {
            $this->builder->commit();
        }

        $this->verbose->level(
            [
                [' <fg=green>done.</fg=green>', true],
                ['<fg=green>Done.</fg=green>', true]
            ]
        );
    }

    /**
     * Creates a build manager for a new Sqon.
     */
    protected function createSqon()
    {
        $this->verbose->level(
            [
                ['Creating a new Sqon...', false],
                ['Creating a new Sqon...', true]
            ]
        );

        $this->builder = Builder::create($this->getConfiguration());

        if (!$this->input->getOption('no-plugins')) {
            $this->builder->registerPlugins();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(
        InputInterface $input,
        OutputInterface $output
    ) {
        $this->input = $input;
        $this->output = $output;
        $this->verbose = $this->getHelper('verbose');

        $this->verbose->setOutput($output);
    }

    /**
     * Creates a new builder for an existing Sqon.
     *
     * @param string $path   The path to the existing Sqon.
     */
    protected function openSqon($path)
    {
        $this->verbose->level(
            [
                ['Editing the Sqon...', false],
                ['Editing the Sqon...', true]
            ]
        );

        $this->builder = Builder::open($path, $this->getConfiguration());

        if (!$this->input->getOption('no-plugins')) {
            $this->builder->registerPlugins();
        }
    }

    /**
     * Sets the PHP bootstrap script for the Sqon.
     */
    protected function setBootstrap()
    {
        $this->verbose->vvln(
            sprintf(
                ' <fg=cyan>?</fg=cyan> Bootstrap Script: %s',
                (null === $this->builder->getConfiguration()->getBootstrap())
                    ? '<fg=magenta>default</fg=magenta>'
                    : '<fg=yellow>custom</fg=yellow>'
            )
        );

        $this->builder->setBootstrap();

        $this->changes++;
    }

    /**
     * Sets the compression mode for new paths.
     */
    protected function setCompression()
    {
        $this->verbose->vvln(
            sprintf(
                ' <fg=cyan>?</fg=cyan> Compression Mode: %s',
                self::$compression[$this->builder->getConfiguration()->getCompression()]
            )
        );

        $this->builder->setCompression();
    }

    /**
     * Sets new paths for the Sqon.
     */
    protected function setPaths()
    {
        $config = $this->builder->getConfiguration();

        $this->verbose->vvln(' <fg=cyan>?</fg=cyan> Paths to set:');

        foreach ($config->getPaths() as $relative => $absolute) {
            if (is_integer($relative)) {
                $this->verbose->vvln(
                    sprintf(
                        '   <fg=yellow>-</fg=yellow> %s',
                        $absolute
                    )
                );
            } else {
                $this->verbose->vvln(
                    sprintf(
                        '   <fg=yellow>-</fg=yellow> %s (as: %s)',
                        $absolute,
                        $relative
                    )
                );
            }
        }

        $this->verbose->vvln(' <fg=magenta>*</fg=magenta> Paths being set:');

        switch ($this->output->getVerbosity()) {
            case OutputInterface::VERBOSITY_QUIET:
            case OutputInterface::VERBOSITY_NORMAL:
                $this->builder->setPaths();

                break;

            case OutputInterface::VERBOSITY_VERBOSE:
            case OutputInterface::VERBOSITY_VERY_VERBOSE:
                $this->setProgressBarStyles();

                $bar = new ProgressBar($this->output);
                $bar->setRedrawFrequency(13);

                $this
                    ->builder
                    ->getEventDispatcher()
                    ->addSubscriber(new ProgressSubscriber($bar))
                ;

                $this->builder->setPaths();

                $bar->finish();
                $this->output->writeln('');

                break;

            case OutputInterface::VERBOSITY_DEBUG:
                $this
                    ->builder
                    ->getEventDispatcher()
                    ->addSubscriber(new ReportSubscriber($this->output))
                ;

                $this->builder->setPaths();

                break;
        }

        $this->changes++;
    }

    /**
     * Sets the primary script for the Sqon.
     */
    protected function setMain()
    {
        $main = $this->builder->getConfiguration()->getMain();

        $this->verbose->vvln(
            sprintf(
                ' <fg=cyan>?</fg=cyan> Main Script: %s',
                (null === $main)
                    ? '<fg=magenta>none</fg=magenta>'
                    : "<fg=yellow>$main</fg=yellow>"
            )
        );

        $this->builder->setMain();

        $this->changes++;
    }

    /**
     * Fixes the paths provided through the command line.
     *
     * @param string   $base  The base directory path.
     * @param string[] $paths The paths to fix.
     *
     * @return string[] The fixed paths.
     */
    private function fixPaths($base, array $paths)
    {
        $match = '/^' . preg_quote($base, '/') . '/';

        foreach ($paths as $i => $path) {
            if (0 < preg_match($match, $path)) {
                unset($paths[$i]);

                $relative = preg_replace($match, '', $path);
                $relative = ltrim($relative, '\\/');

                $paths[$relative] = $path;
            }
        }

        return $paths;
    }

    /**
     * Returns a new build configuration manager.
     *
     * If a command line option is provided, its value will override the
     * respective build setting in the build configuration file.
     *
     * @return Configuration The build configuration manager.
     */
    private function getConfiguration()
    {
        $base = getcwd();

        if (null !== $this->input->getOption('base')) {
            $base = $this->input->getOption('base');
        }

        $settings = ['sqon' => []];
        $override = [
            'bootstrap' => 'bootstrap',
            'compression' => 'compression',
            'main' => 'main',
            'path' => 'paths',
            'shebang' => 'shebang'
        ];

        foreach ($override as $option => $setting) {
            if ($this->input->hasParameterOption("--$option")) {
                $settings['sqon'][$setting] = $this->input->getOption($option);
            }
        }

        if (isset($settings['sqon']['paths'])) {
            $settings['sqon']['paths'] = $this->fixPaths(
                $base,
                $settings['sqon']['paths']
            );
        }

        if (null !== $this->input->getOption('config')) {
            list($base, $settings) = $this->parseConfig(
                $settings,
                $this->input->getOption('config')
            );
        }

        return new Configuration($base, $settings);
    }

    /**
     * Parses a build configuration file and returns its data.
     *
     * @param array  $settings The override settings.
     * @param string $file     The path to the build configuration file.
     *
     * @return array The build configuration base directory and settings.
     *
     * @throws InvalidArgumentException If the file could not be found.
     */
    private function parseConfig(array $settings, $file)
    {
        if (is_relative($file)) {
            $file = getcwd() . DIRECTORY_SEPARATOR . $file;
        }

        $file = canonicalize($file);

        if (is_file($file)) {
            $data = Yaml::parse((new File($file, 'r'))->read());
        } elseif (is_file($file . '.dist')) {
            $data = Yaml::parse((new File($file . '.dist', 'r'))->read());

        // @codeCoverageIgnoreStart
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'The build configuration file "%s" (or "%s.dist") could not be found.',
                    $file,
                    $file
                )
            );
        }
        // @codeCoverageIgnoreEnd

        /*
        if (isset($data['sqon'])) {
            $data['sqon'] = array_merge($data['sqon'], $settings['sqon']);
        } else {
            $data['sqon'] = $settings['sqon'];
        }
        */

        $data['sqon'] = isset($data['sqon'])
            ? array_merge($data['sqon'], $settings['sqon'])
            : $settings['sqon'];

        return [dirname($file), $data];
    }

    /**
     * Sets the styles for the progress bar.
     */
    private function setProgressBarStyles()
    {
        // Set progress bar styles.
        $formatter = $this->output->getFormatter();
        $formatter->setStyle('b', new OutputFormatterStyle('blue'));
        $formatter->setStyle('c', new OutputFormatterStyle('green'));
        $formatter->setStyle('m', new OutputFormatterStyle('magenta'));
        $formatter->setStyle('r', new OutputFormatterStyle('cyan'));

        // Set default formatting styles.
        ProgressBar::setFormatDefinition(
            'verbose_nomax',
            '   <b>[</b><r>%bar%</r><b>]</b> <c>%current%</c> - <c>%elapsed:6s%</c>'
        );

        ProgressBar::setFormatDefinition(
            'very_verbose_nomax',
            '   <b>[</b><r>%bar%</r><b>]</b> <c>%current%</c> - <c>%elapsed:6s%</c> - <m>%memory:6s%</m>'
        );
    }
}
