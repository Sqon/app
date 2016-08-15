<?php

namespace Test\Sqon\Builder\Event;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_TestCase as TestCase;
use Sqon\Builder\Event\ReportSubscriber;
use Sqon\Event\BeforeSetPathEvent;
use Sqon\Path\PathInterface;
use Sqon\SqonInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Verifies that the path reporter functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \Sqon\Builder\Event\ReportSubscriber
 */
class ReportSubscriberTest extends TestCase
{
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
     * The output manager.
     *
     * @var MockObject|OutputInterface
     */
    private $output;

    /**
     * The Sqon manager mock.
     *
     * @var MockObject|SqonInterface
     */
    private $sqon;

    /**
     * Verify that the path is reported.
     */
    public function testReportCurrentPath()
    {
        $this
            ->output
            ->expects($this->once())
            ->method('writeln')
            ->with('   <fg=yellow>+</fg=yellow> test.php');
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
        $this->dispatcher = new EventDispatcher();
        $this->manager = $this->getMockForAbstractClass(PathInterface::class);
        $this->output = $this->getMockForAbstractClass(OutputInterface::class);
        $this->sqon = $this->getMockForAbstractClass(SqonInterface::class);

        $this->dispatcher->addSubscriber(new ReportSubscriber($this->output));
    }
}
