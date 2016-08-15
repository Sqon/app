<?php

namespace Sqon\Builder;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Sqon\Builder\Exception\BuilderException;
use Sqon\Iterator\DirectoryIterator;
use Sqon\Path\File;
use Sqon\Path\Memory;
use Sqon\Sqon;
use Sqon\SqonInterface;

use function Sqon\is_relative;

/**
 * Manages the Sqon build process using a configuration manager.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Builder implements BuilderInterface
{
    /**
     * The build configuration manager.
     *
     * @var ConfigurationInterface
     */
    private $config;

    /**
     * The Sqon manager.
     *
     * @var SqonInterface
     */
    private $sqon;

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        $this->sqon->commit();
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ConfigurationInterface $config)
    {
        $path = self::getAbsolutePath($config, $config->getOutput());

        if (file_exists($path)) {
            if (!unlink($path)) {
                // @codeCoverageIgnoreStart
                throw new BuilderException(
                    "The path \"$path\" could not be deleted."
                );
                // @codeCoverageIgnoreEnd
            }
        }

        return new self($config, Sqon::create($path));
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventDispatcher()
    {
        return $this->sqon->getEventDispatcher();
    }

    /**
     * {@inheritdoc}
     */
    public function getSqon()
    {
        return $this->sqon;
    }

    /**
     * {@inheritdoc}
     */
    public static function open($path, ConfigurationInterface $config)
    {
        return new self($config, Sqon::open($path));
    }

    /**
     * {@inheritdoc}
     */
    public function registerPlugins()
    {
        $this->config->registerPlugins($this->sqon->getEventDispatcher());

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setBootstrap()
    {
        if (null === $this->config->getBootstrap()) {
            $script = Sqon::createBootstrap($this->config->getShebang());
        } else {
            $script = $this->config->getBootstrap();
        }

        $this->sqon->setBootstrap($script);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCompression()
    {
        $this->sqon->setCompression($this->config->getCompression());

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setMain()
    {
        if (null === $this->config->getMain()) {
            $this->sqon->removePath(Sqon::PRIMARY);
        } else {
            $this->sqon->setPath(
                Sqon::PRIMARY,
                new Memory(
                    sprintf(
                        "<?php chdir(dirname(__DIR__)); require '%s';",
                        $this->config->getMain()
                    )
                )
            );
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPaths()
    {
        foreach ($this->config->getPaths() as $relative => $absolute) {
            if (is_integer($relative)) {
                $alternative = '';
                $relative = $absolute;
            } else {
                $alternative = $relative;
            }

            $absolute = self::getAbsolutePath($this->config, $absolute);

            if (is_dir($absolute)) {
                $this->sqon->setPathsUsingIterator(
                    new DirectoryIterator(
                        $absolute,
                        ('' === $alternative)
                            ? $this->config->getDirectory()
                            : $absolute,
                        $alternative
                    )
                );
            } else {
                $this->sqon->setPath($relative, new File($absolute));
            }
        }

        return $this;
    }

    /**
     * Initializes the new Sqon builder.
     *
     * @param ConfigurationInterface $config The build configuration manager.
     * @param SqonInterface          $sqon   The Sqon manager.
     */
    private function __construct(
        ConfigurationInterface $config,
        SqonInterface $sqon
    ) {
        $this->config = $config;
        $this->sqon = $sqon;

        $sqon->setEventDispatcher(new EventDispatcher());
    }

    /**
     * Converts a relative path into an absolute path.
     *
     * @param ConfigurationInterface $config The build configuration manager.
     * @param string                 $path   The path that could be relative.
     *
     * @return string The absolute path.
     */
    private static function getAbsolutePath(
        ConfigurationInterface $config,
        $path
    ) {
        if (is_relative($path)) {
            return $config->getDirectory() . DIRECTORY_SEPARATOR . $path;
        }

        return $path;
    }
}
