<?php

namespace Sqon\Console\Helper;

use InvalidArgumentException;
use KHerGe\File\File;
use RuntimeException;
use Sqon\Builder\Configuration;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Yaml\Yaml;

use function Sqon\canonicalize;
use function Sqon\is_relative;

/**
 * Provides utility methods for handling build configuration data.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class ConfigHelper extends Helper
{
    /**
     * The default name for the configuration file.
     *
     * @var string
     */
    const FILE = 'sqon.yml';

    /**
     * Returns a new build configuration manager for a file path.
     *
     * @param null|string $path The path to the build configuration file.
     *
     * @return Configuration The build configuration manager.
     */
    public function loadConfig($path)
    {
        if (null === $path) {
            $path = $this->findConfig();
        } elseif (is_file($path)) {
            if (is_relative($path)) {
                $path = getcwd() . DIRECTORY_SEPARATOR . $path;
            }

            $path = canonicalize($path);

        // @codeCoverageIgnoreStart
        } else {
            throw new InvalidArgumentException(
                "The configuration file \"$path\" does not exist."
            );
        }
        // @codeCoverageIgnoreEnd

        $contents = (new File($path, 'r'))->read();
        $settings = Yaml::parse($contents) ?: [];

        return new Configuration(dirname($path), $settings);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getName()
    {
        return 'config';
    }

    /**
     * Finds the configuration file in the current directory tree.
     *
     * @return string The path to the configuration file.
     *
     * @throws RuntimeException If the file could not be found.
     */
    private function findConfig()
    {
        $dir = getcwd();

        while (true) {
            $path = $dir . DIRECTORY_SEPARATOR . self::FILE;

            if (file_exists($path)) {
                return $path;
            }

            $path .= '.dist';

            if (file_exists($path)) {
                return $path;
            }

            $up = dirname($dir);

            // @codeCoverageIgnoreStart
            if ($up === $dir) {
                break;
            }
            // @codeCoverageIgnoreEnd

            $dir = $up;
        }

        // @codeCoverageIgnoreStart
        throw new RuntimeException(
            sprintf(
                'The configuration file "%s" (or "%s.dist") could not be found.',
                self::FILE,
                self::FILE
            )
        );
        // @codeCoverageIgnore
    }
}
