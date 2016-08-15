<?php

namespace Test\Sqon\Builder\Event;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_TestCase as TestCase;
use Sqon\Builder\Event\ProgressSubscriber;
use Sqon\Event\BeforeSetPathEvent;
use Sqon\Path\PathInterface;
use Sqon\SqonInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Verifies that the progress bar subscriber functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \Sqon\Builder\Event\ProgressSubscriber
 */
class ProgressSubscriberTest extends TestCase
{
    /**
     * The progress bar manager mock.
     *
     * @var MockObject|ProgressBar
     */
    private $bar;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * The path manager mock.
     *
     * @var MockObject|PathInterface
     */
    private $manager;

    /**
     * The Sqon manager mock.
     *
     * @var MockObject|SqonInterface
     */
    private $sqon;

    /**
     * Verify that the progress bar is updated.
     */
    public function testUpdateTheProgressBar()
    {
        $this
            ->bar
            ->expects($this->once())
            ->method('advance')
        ;

        $this->dispatcher->dispatch(
            BeforeSetPathEvent::NAME,
            new BeforeSetPathEvent(
                $this->sqon,
                'test.php',
                $this->manager
            )
        );
    }

    /**
     * Registers the subscriber.
     */
    protected function setUp()
    {
        $this->bar = $this
            ->getMockBuilder(ProgressBar::class)
            ->disableOriginalConstructor()
            ->setMethods(['advance'])
            ->getMockForAbstractClass()
        ;

        $this->dispatcher = new EventDispatcher();
        $this->dispatcher->addSubscriber(new ProgressSubscriber($this->bar));

        $this->manager = $this->getMockForAbstractClass(PathInterface::class);

        $this->sqon = $this->getMockForAbstractClass(SqonInterface::class);
    }
}
