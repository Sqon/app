<?php

namespace Test\Sqon\Builder\Plugin;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_TestCase as TestCase;
use Sqon\Builder\ConfigurationInterface;
use Sqon\Builder\Plugin\Chmod;
use Sqon\Event\Subscriber\ChmodSubscriber;
use Sqon\SqonInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Verifies that the chmod plugin functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class ChmodTest extends TestCase
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
     * @var Chmod
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
                'mode' => 0755
            ],
            (new Processor())->processConfiguration(
                $this->plugin,
                [
                    0755
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
        $this
            ->config
            ->expects(self::once())
            ->method('getSettings')
            ->with('chmod')
            ->willReturn(0755)
        ;

        $this
            ->dispatcher
            ->expects(self::once())
            ->method('addSubscriber')
            ->with(self::isInstanceOf(ChmodSubscriber::class))
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

        $this->plugin = new Chmod();

        $this->sqon = $this->getMockForAbstractClass(SqonInterface::class);
    }
}
