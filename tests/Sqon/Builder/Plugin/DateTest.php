<?php

namespace Test\Sqon\Builder\Plugin;

use DateTime;
use DateTimeZone;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_TestCase as TestCase;
use Sqon\Builder\ConfigurationInterface;
use Sqon\Builder\Plugin\Date;
use Sqon\SqonInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Verifies that the Date plugin functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \Sqon\Builder\Plugin\AbstractReplaceExtension
 * @covers \Sqon\Builder\Plugin\Date
 */
class DateTest extends TestCase
{
    /**
     * The build configuration manager mock.
     *
     * @var ConfigurationInterface|MockObject
     */
    private $config;

    /**
     * The event dispatcher mock.
     *
     * @var EventDispatcherInterface|MockObject
     */
    private $dispatcher;

    /**
     * The plugin.
     *
     * @var Date
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
                [
                    'format' => 'c',
                    'pattern' => '/a/',
                    'when' => 'now',
                    'zone' => 'UTC'
                ],
                [
                    'format' => 'b',
                    'pattern' => '/a/',
                    'when' => 'now',
                    'zone' => 'UTC'
                ],
                [
                    'format' => 'b',
                    'pattern' => '/a/',
                    'when' => 'c',
                    'zone' => 'UTC'
                ],
                [
                    'format' => 'b',
                    'pattern' => '/a/',
                    'when' => 'c',
                    'zone' => 'd'
                ]
            ],
            (new Processor())->processConfiguration(
                $this->plugin,
                [
                    [
                        [
                            'pattern' => '/a/'
                        ],

                        [
                            'format' => 'b',
                            'pattern' => '/a/'
                        ],

                        [
                            'format' => 'b',
                            'pattern' => '/a/',
                            'when' => 'c'
                        ],

                        [
                            'format' => 'b',
                            'pattern' => '/a/',
                            'when' => 'c',
                            'zone' => 'd'
                        ]
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
        $this
            ->config
            ->expects(self::at(0))
            ->method('getSettings')
            ->with('replace')
            ->willReturn(
                [
                    'all' => [
                        [
                            'pattern' => '/a/',
                            'replacement' => "A: %s"
                        ]
                    ],
                    'path' => [
                        [
                            'path' => 'src/build.php',
                            'pattern' => '/b/',
                            'replacement' => "B: %s"
                        ],
                        [
                            'path' => 'src/build.php',
                            'pattern' => '/c/',
                            'replacement' => "C: %s"
                        ]
                    ],
                    'pattern' => [
                        [
                            'path' => '/\.php$/',
                            'pattern' => '/d/',
                            'replacement' => '%s'
                        ]
                    ]
                ]
            )
        ;

        $this
            ->config
            ->expects(self::at(1))
            ->method('getSettings')
            ->with('date')
            ->willReturn(
                [
                    [
                        'format' => 'c',
                        'pattern' => '/a/',
                        'when' => 'now',
                        'zone' => 'UTC'
                    ],

                    [
                        'format' => 'r',
                        'pattern' => '/b/',
                        'when' => 'now',
                        'zone' => 'UTC'
                    ],

                    [
                        'format' => 'r',
                        'pattern' => '/c/',
                        'when' => 'tomorrow',
                        'zone' => 'UTC'
                    ],

                    [
                        'format' => 'r',
                        'pattern' => '/d/',
                        'when' => 'tomorrow',
                        'zone' => 'America/Los_Angeles'
                    ]
                ]
            )
        ;

        $a = (new DateTime())->format('c');
        $b = (new DateTime())->format('r');
        $c = (new DateTime('tomorrow'))->format('r');
        $d = (new DateTime('tomorrow', new DateTimeZone('America/Los_Angeles')))
            ->format('r')
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
                            'pattern' => '/a/',
                            'replacement' => "A: $a"
                        ]
                    ],
                    'path' => [
                        [
                            'path' => 'src/build.php',
                            'pattern' => '/b/',
                            'replacement' => "B: $b"
                        ],
                        [
                            'path' => 'src/build.php',
                            'pattern' => '/c/',
                            'replacement' => "C: $c"
                        ]
                    ],
                    'pattern' => [
                        [
                            'path' => '/\.php$/',
                            'pattern' => '/d/',
                            'replacement' => $d
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
        $this->config = $this->getMockForAbstractClass(
            ConfigurationInterface::class
        );

        $this->dispatcher = $this->getMockForAbstractClass(
            EventDispatcherInterface::class
        );

        $this->plugin = new Date();

        $this->sqon = $this->getMockForAbstractClass(SqonInterface::class);
    }
}
