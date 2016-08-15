<?php

namespace Sqon\Builder\Event;

use Sqon\Event\BeforeSetPathEvent;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Reports the path that is about to be set in the Sqon.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class ReportSubscriber implements EventSubscriberInterface
{
    /**
     * The output manager.
     *
     * @var OutputInterface
     */
    private $output;

    /**
     * Initializes the new report subscriber.
     *
     * @param OutputInterface $output The output manager.
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Updates the progress bar.
     *
     * @param BeforeSetPathEvent $event The event manager.
     */
    public function beforeSetPath(BeforeSetPathEvent $event)
    {
        $this->output->writeln(
            sprintf(
                '   <fg=yellow>+</fg=yellow> %s',
                $event->getPath()
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            BeforeSetPathEvent::NAME => [
                ['beforeSetPath', 100]
            ]
        ];
    }
}
