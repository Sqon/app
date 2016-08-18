<?php

namespace Sqon\Builder\Plugin;

use Sqon\Builder\ConfigurationInterface;
use Sqon\Builder\Exception\Builder\PluginException;
use Sqon\Event\BeforeSetPathEvent;
use Sqon\Event\Subscriber\ReplaceSubscriber;
use Sqon\SqonInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface as SchemaInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Makes Git information available for replacement.
 *
 * The Git plugin modifies the settings for the Replace plugin so that patterns
 * can be replaced with Git information. The Git plugin supports adding commit
 * hashes, short commit hashes, commit dates, current commit tags, and most
 * recent commit tags.
 *
 * ```php
 * [
 *     // Git replacement settings.
 *     'git' => [
 *
 *         // 18122de050f7ca1246045e04c0376294f3d0288b
 *         'commit' => ['/\$commit\$/'],
 *
 *         // 18122de
 *         'commit-date' => ['/\$commit-date\$/'],
 *
 *         // 2016-08-16 21:23:55 -0700
 *         'commit-short' => ['/\$commit-short\$/'],
 *
 *         // 0.2.0
 *         'commit-tag' => ['/\$commit-tag\$/'],
 *
 *         // 0.2.0-2-g18122de
 *         'tag' => ['/\$tag\$/'],
 *
 *     ],
 *
 *     // The corresponding replacement plugin settings.
 *     'replace' => [
 *
 *         // Replace for all paths.
 *         'all' => [
 *             '/\$tag\$/' => 'Tag: %s'
 *         ],
 *
 *         // Replace for a specific path.
 *         'path' => [
 *             'src/build.php' => [
 *                 '/\$commit-date\$/' => 'Date: %s',
 *                 '/\$commit\$/' => 'Commit: %s',
 *                 '/\$commit-tag\$/' => 'Version: %s'
 *             ]
 *         ],
 *
 *         // Replace for paths matching a pattern.
 *         'pattern' => [
 *             '/\.php$/' => [
 *                 '/\$commit-short\$/' => '%s'
 *             ]
 *         ]
 *
 *     ]
 * ]
 * ```
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Git implements PluginInterface, SchemaInterface
{
    /**
     * The current commit hash.
     *
     * @var string
     */
    private $commit;

    /**
     * The current commit date.
     *
     * @var string
     */
    private $commitDate;

    /**
     * The shortened commit hash.
     *
     * @var string
     */
    private $commitShort;

    /**
     * The current commit tag.
     *
     * @var string
     */
    private $commitTag;

    /**
     * The current tag.
     *
     * @var string
     */
    private $tag;

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $root = $tree->root('git');

        $patterns = function ($type) {
            $tree = new TreeBuilder();
            $root = $tree->root($type);

            $root
                ->prototype('scalar')
                    ->cannotBeEmpty()
                    ->isRequired()
                ->end()
            ;

            return $root;
        };

        $root
            ->normalizeKeys(false)
            ->children()
                ->append($patterns('commit'))
                ->append($patterns('commit-date'))
                ->append($patterns('commit-short'))
                ->append($patterns('commit-tag'))
                ->append($patterns('tag'))
            ->end()
        ;

        return $tree;
    }

    /**
     * {@inheritdoc}
     */
    public function register(
        EventDispatcherInterface $dispatcher,
        ConfigurationInterface $config,
        SqonInterface $sqon
    ) {
        $this->checkReplace($dispatcher);

        $replace = $config->getSettings('replace');

        // @codeCoverageIgnoreStart
        if (empty($replace)) {
            return;
        }
        // @codeCoverageIgnoreEnd

        foreach ($config->getSettings('git') as $type => $patterns) {
            // @codeCoverageIgnoreStart
            if (empty($patterns)) {
                continue;
            }
            // @codeCoverageIgnoreEnd

            $value = null;

            switch ($type) {
                case 'commit':
                    $value = $this->getCommit($config->getDirectory());

                    break;

                case 'commit-date':
                    $value = $this->getCommitDate($config->getDirectory());

                    break;

                case 'commit-short':
                    $value = $this->getCommitShort($config->getDirectory());

                    break;

                case 'commit-tag':
                    $value = $this->getCommitTag($config->getDirectory());

                    break;

                case 'tag':
                    $value = $this->getTag($config->getDirectory());

                    break;
            }

            if (null !== $value) {
                $this->replacePatterns($replace, (array) $patterns, $value);
            }
        }

        $config->setSettings('replace', $replace);
    }

    /**
     * Throws an exception if the replace subscriber is already registered.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     *
     * @throws PluginException If the subscriber could not be found.
     */
    private function checkReplace(EventDispatcherInterface $dispatcher)
    {
        $listeners = $dispatcher->getListeners(BeforeSetPathEvent::NAME);

        foreach ((array) $listeners as $listener) {
            // @codeCoverageIgnoreStart
            if ($listener[0] instanceof ReplaceSubscriber) {
                throw new PluginException(
                    'The Replace plugin must be registered after Git.'
                );
            }
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Returns the current commit hash.
     *
     * @param string $base The base directory path.
     *
     * @return string The current commit hash.
     */
    private function getCommit($base)
    {
        if (null === $this->commit) {
            $this->commit = $this->run(
                $base,
                ['git', 'log', '--pretty="%H"', '-n1', 'HEAD']
            );
        }

        return $this->commit;
    }

    /**
     * Returns the current commit date.
     *
     * @param string $base The base directory path.
     *
     * @return string The current commit date.
     */
    private function getCommitDate($base)
    {
        if (null === $this->commitDate) {
            $this->commitDate = $this->run(
                $base,
                ['git', 'log', '--pretty="%ci"', '-n1', 'HEAD']
            );
        }

        return $this->commitDate;
    }

    /**
     * Returns the shortened current commit hash.
     *
     * @param string $base The base directory path.
     *
     * @return string The shortened current commit hash.
     */
    private function getCommitShort($base)
    {
        if (null === $this->commitShort) {
            $this->commitShort = substr($this->getCommit($base), 0, 7);
        }

        return $this->commitShort;
    }

    /**
     * Returns the current exact tag.
     *
     * @param string $base The base directory path.
     *
     * @return string The current exact tag.
     */
    private function getCommitTag($base)
    {
        if (null === $this->commitTag) {
            $this->commitTag = $this->run(
                $base,
                ['git', 'describe', '--tags', '--exact-match', 'HEAD']
            );
        }

        return $this->commitTag;
    }

    /**
     * Returns the current tag.
     *
     * @param string $base The base directory path.
     *
     * @return string The current tag.
     */
    private function getTag($base)
    {
        if (null === $this->tag) {
            $this->tag = $this->run(
                $base,
                ['git', 'describe', '--tags', 'HEAD']
            );
        }

        return $this->tag;
    }


    /**
     * Replaces the placeholder values with ab actual Git value.
     *
     * @param array  &$array   The replacement plugin settings.
     * @param array  $patterns The Git patterns to replace.
     * @param string $value    The Git value to replace with.
     */
    private function replacePatterns(array &$array, array $patterns, $value)
    {
        $set = function ($key, $pattern, $value) use (&$array) {
            foreach ($array[$key] as &$set) {
                if ($pattern === $set['pattern']) {
                    $set['replacement'] = sprintf(
                        $set['replacement'],
                        $value
                    );
                }
            }
        };

        foreach ($patterns as $pattern) {
            $set('all', $pattern, $value);
            $set('path', $pattern, $value);
            $set('pattern', $pattern, $value);
        }
    }

    /**
     * Runs a git command returns the output.
     *
     * @param string $dir     The repository directory.
     * @param array  $command The command arguments.
     *
     * @return string The command output.
     *
     * @throws PluginException If the command was not executed successfully.
     */
    private function run($dir, array $command)
    {
        $process = (new ProcessBuilder($command))
            ->setWorkingDirectory($dir)
            ->getProcess()
        ;

        $process->run();

        if (!$process->isSuccessful()) {
            // @codeCoverageIgnoreStart
            throw new PluginException($process->getErrorOutput());
            // @codeCoverageIgnoreEnd
        }

        return trim($process->getOutput(), "\" \r\n");
    }
}
