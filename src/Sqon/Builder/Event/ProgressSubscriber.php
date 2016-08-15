<?php

namespace Sqon\Builder\Event;

use Sqon\Event\BeforeSetPathEvent;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Updates a progress bar when a path is about to be set.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class ProgressSubscriber implements EventSubscriberInterface
{
    /**
     * The progress bar manager.
     *
     * @var ProgressBar
     */
    private $bar;

    /**
     * Initializes the new progress bar subscriber.
     *
     * @param ProgressBar $bar The progress bar manager.
     */
    public function __construct(ProgressBar $bar)
    {
        $this->bar = $bar;
    }

    /**
     * Updates the progress bar.
     *
     * @param BeforeSetPathEvent $event The event manager.
     */
    public function beforeSetPath(BeforeSetPathEvent $event)
    {
        $this->bar->setMessage($event->getPath());
        $this->bar->advance();
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
