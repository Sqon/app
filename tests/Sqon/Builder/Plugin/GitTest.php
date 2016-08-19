<?php

namespace Test\Sqon\Builder\Plugin;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_TestCase as TestCase;
use Sqon\Builder\ConfigurationInterface;
use Sqon\Builder\Plugin\Git;
use Sqon\SqonInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Test\Sqon\Test\TempTrait;

/**
 * Verifies that the Git plugin functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \Sqon\Builder\Plugin\AbstractReplaceExtension
 * @covers \Sqon\Builder\Plugin\Git
 */
class GitTest extends TestCase
{
    use TempTrait;

    /**
     * The build configuration manager mock.
     *
     * @var ConfigurationInterface|MockObject
     */
    private $config;

    /**
     * The current working directory.
     *
     * @var string
     */
    private $dir;

    /**
     * The event dispatcher mock.
     *
     * @var EventDispatcherInterface|MockObject
     */
    private $dispatcher;

    /**
     * The plugin.
     *
     * @var Git
     */
    private $plugin;

    /**
     * The Sqon manager mock.
     *
     * @var MockObject|SqonInterface
     */
    private $sqon;

    /**
     * Verify that the settings are processed correctly.
     */
    public function testConfigurationSettingsAreProcessedCorrectly()
    {
        self::assertEquals(
            [
                'commit' => ['a', 'b'],
                'commit-date' => ['c', 'd'],
                'commit-short' => ['e', 'f'],
                'commit-tag' => ['g', 'h'],
                'tag' => ['i', 'j']
            ],
            (new Processor())->processConfiguration(
                $this->plugin,
                [
                    [
                        'commit' => ['a', 'b'],
                        'commit-date' => ['c', 'd'],
                        'commit-short' => ['e', 'f'],
                        'commit-tag' => ['g', 'h'],
                        'tag' => ['i', 'j']
                    ]
                ]
            ),
            'The configuration settings were not processed correctly.'
        );
    }

    /**
     * Verify that Replace plugin settings are updated with Git values.
     */
    public function testReplacePluginPlaceholderSettingsWithGitValues()
    {
        $commit = exec('git log --pretty="%H" -n1 HEAD');
        $commitDate = exec('git log --pretty="%ci" -n1 HEAD');
        $commitShort = exec('git log --pretty="%h" -n1 HEAD');
        $commitTag = exec('git describe --tags --exact-match HEAD');
        $tag = exec('git describe --tags HEAD');

        $this
            ->config
            ->expects(self::at(0))
            ->method('getSettings')
            ->with('replace')
            ->willReturn(
                [
                    'all' => [
                        [
                            'pattern' => '/\$tag\$/',
                            'replacement' => "Tag: %s"
                        ]
                    ],
                    'path' => [
                        [
                            'path' => 'src/build.php',
                            'pattern' => '/\$commit-date\$/',
                            'replacement' => "Date: %s"
                        ],
                        [
                            'path' => 'src/build.php',
                            'pattern' => '/\$commit\$/',
                            'replacement' => "Commit: %s"
                        ],
                        [
                            'path' => 'src/build.php',
                            'pattern' => '/\$commit-tag\$/',
                            'replacement' => "Version: %s"
                        ]
                    ],
                    'pattern' => [
                        [
                            'path' => '/\.php$/',
                            'pattern' => '/\$commit-short\$/',
                            'replacement' => "%s"
                        ]
                    ]
                ]
            )
        ;

        $this
            ->config
            ->expects(self::at(1))
            ->method('getSettings')
            ->with('git')
            ->willReturn(
                [
                    'commit' => ['/\$commit\$/'],
                    'commit-date' => ['/\$commit-date\$/'],
                    'commit-short' => ['/\$commit-short\$/'],
                    'commit-tag' => ['/\$commit-tag\$/'],
                    'tag' => ['/\$tag\$/'],
                ]
            )
        ;

        $this
            ->config
            ->expects(self::once())
            ->method('setSettings')
            ->with(
                'replace',
                [
                    'all' => [
                        [
                            'pattern' => '/\$tag\$/',
                            'replacement' => "Tag: $tag"
                        ]
                    ],
                    'path' => [
                        [
                            'path' => 'src/build.php',
                            'pattern' => '/\$commit-date\$/',
                            'replacement' => "Date: $commitDate"
                        ],
                        [
                            'path' => 'src/build.php',
                            'pattern' => '/\$commit\$/',
                            'replacement' => "Commit: $commit"
                        ],
                        [
                            'path' => 'src/build.php',
                            'pattern' => '/\$commit-tag\$/',
                            'replacement' => "Version: $commitTag"
                        ]
                    ],
                    'pattern' => [
                        [
                            'path' => '/\.php$/',
                            'pattern' => '/\$commit-short\$/',
                            'replacement' => $commitShort
                        ]
                    ]
                ]
            )
        ;

        $this->plugin->register(
            $this->dispatcher,
            $this->config,
            $this->sqon
        );
    }

    /**
     * Creates a new instance of the plugin.
     */
    protected function setUp()
    {
        $this->dir = getcwd();

        $this->createRepo();

        $this->config = $this->getMockForAbstractClass(
            ConfigurationInterface::class
        );

        $this->dispatcher = $this->getMockForAbstractClass(
            EventDispatcherInterface::class
        );

        $this->plugin = new Git();

        $this->sqon = $this->getMockForAbstractClass(SqonInterface::class);
    }

    /**
     * Restores the current working directory.
     */
    protected function tearDown()
    {
        chdir($this->dir);

        $this->deleteTempPaths();
    }

    /**
     * Creates a new Git repository.
     */
    private function createRepo()
    {
        $dir = $this->createTempDirectory();

        chdir($dir);
        touch($dir . '/test');
        exec('git init');
        exec('git config user.name "Test"');
        exec('git config user.email test@test');
        exec('git add test');
        exec('git commit -m "Test commit."');
        exec('git tag 1.0.0');
    }
}
