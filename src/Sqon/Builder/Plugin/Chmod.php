<?php

namespace Sqon\Builder\Plugin;

use Sqon\Builder\ConfigurationInterface;
use Sqon\Event\Subscriber\ChmodSubscriber;
use Sqon\SqonInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Registers `ChmodSubscriber` as a Sqon builder plugin.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Chmod implements PluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(
        EventDispatcherInterface $dispatcher,
        ConfigurationInterface $config,
        SqonInterface $sqon
    ) {
        $dispatcher->addSubscriber(
            new ChmodSubscriber(
                $config->getSettings('chmod')
            )
        );
    }
}
