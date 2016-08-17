<?php

namespace Test\Sqon\Builder\Plugin;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_TestCase as TestCase;
use Sqon\Builder\ConfigurationInterface;
use Sqon\Builder\Plugin\Replace;
use Sqon\Event\Subscriber\ReplaceSubscriber;
use Sqon\SqonInterface;
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
                        '/pattern/' => 'replacement'
                    ],
                    'path' => [
                        'to/path.php' => [
                            '/pattern/' => 'replacement'
                        ]
                    ],
                    'pattern' => [
                        '/path/' => [
                            '/pattern/' => 'replacement'
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
