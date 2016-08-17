<?php

namespace Test\Sqon\Builder\Plugin;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_TestCase as TestCase;
use Sqon\Builder\ConfigurationInterface;
use Sqon\Builder\Plugin\Filter;
use Sqon\Event\Subscriber\FilterSubscriber;
use Sqon\SqonInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Verifies that the filter plugin functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class FilterTest extends TestCase
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
     * @var Filter
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
        $expected = (new FilterSubscriber())
            ->excludeByName('exclude.php')
            ->excludeByPath('path/to/exclude')
            ->excludeByPattern('/exclude/')
            ->includeByName('include.php')
            ->includeByPath('path/to/include')
            ->includeByPattern('/include/')
        ;

        $this
            ->config
            ->expects(self::once())
            ->method('getSettings')
            ->with('filter')
            ->willReturn(
                [
                    'exclude' => [
                        'name' => ['exclude.php'],
                        'path' => ['path/to/exclude'],
                        'pattern' => ['/exclude/']
                    ],
                    'include' => [
                        'name' => ['include.php'],
                        'path' => ['path/to/include'],
                        'pattern' => ['/include/']
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
                    function (FilterSubscriber $actual) use ($expected) {
                        self::assertEquals(
                            $expected,
                            $actual,
                            'The filter subscriber was not configured correctly.'
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

        $this->plugin = new Filter();

        $this->sqon = $this->getMockForAbstractClass(SqonInterface::class);
    }
}
