<?php

namespace Test\Sqon\Builder\Plugin;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_TestCase as TestCase;
use Sqon\Builder\ConfigurationInterface;
use Sqon\Builder\Plugin\Replace;
use Sqon\Event\Subscriber\ReplaceSubscriber;
use Sqon\SqonInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Verifies that the Replace plugin functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class ReplaceTest extends TestCase
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
     * @var Replace
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
                'all' => [
                    [
                        'pattern' => 'a',
                        'replacement' => 'b'
                    ],
                    [
                        'pattern' => 'c',
                        'replacement' => 'd'
                    ]
                ],
                'path' => [
                    [
                        'path' => 'path.php',
                        'pattern' => 'e',
                        'replacement' => 'f'
                    ],
                    [
                        'path' => 'path.php',
                        'pattern' => 'g',
                        'replacement' => 'h'
                    ]
                ],
                'pattern' => [
                    [
                        'path' => '/path/',
                        'pattern' => 'i',
                        'replacement' => 'j'
                    ],
                    [
                        'path' => '/path/',
                        'pattern' => 'k',
                        'replacement' => 'l'
                    ]
                ]
            ],
            (new Processor())->processConfiguration(
                $this->plugin,
                [
                    [
                        'all' => [
                            'a' => 'b',
                            'c' => 'd'
                        ],
                        'path' => [
                            'path.php' => [
                                'e' => 'f',
                                'g' => 'h'
                            ]
                        ],
                        'pattern' => [
                            '/path/' => [
                                'i' => 'j',
                                'k' => 'l'
                            ]
                        ]
                    ]
                ]
            ),
            'The configuration settings were not processed correctly.'
        );
    }

    /**
     * Verify that the plugin subscriber is registered.
     */
    public function testSubscriberForThePluginIsRegistered()
    {
        $expected = (new ReplaceSubscriber())
            ->replaceAll('/pattern/', 'replacement')
            ->replaceByPath('to/path.php', '/pattern/', 'replacement')
            ->replaceByPattern('/path/', '/pattern/', 'replacement')
        ;

        $this
            ->config
            ->expects(self::once())
            ->method('getSettings')
            ->with('replace')
            ->willReturn(
                [
                    'all' => [
                        [
                            'pattern' => '/pattern/',
                            'replacement' => 'replacement'
                        ]
                    ],
                    'path' => [
                        [
                            'path' => 'to/path.php',
                            'pattern' => '/pattern/',
                            'replacement' => 'replacement'
                        ]
                    ],
                    'pattern' => [
                        [
                            'path' => '/path/',
                            'pattern' => '/pattern/',
                            'replacement' => 'replacement'
                        ]
                    ]
                ]
            )
        ;

        $this
            ->dispatcher
            ->expects(self::once())
            ->method('addSubscriber')
            ->with(
                self::callback(
                    function (ReplaceSubscriber $actual) use ($expected) {
                        self::assertEquals(
                            $expected,
                            $actual,
                            'The replacement subscriber was not configured correctly.'
                        );

                        return true;
                    }
                )
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

        $this->plugin = new Replace();

        $this->sqon = $this->getMockForAbstractClass(SqonInterface::class);
    }
}
