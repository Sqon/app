<?php

namespace Sqon\Builder\Plugin;

use Sqon\Builder\ConfigurationInterface;
use Sqon\Event\Subscriber\FilterSubscriber;
use Sqon\SqonInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Registers `FilterSubscriber` as a Sqon builder plugin.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Filter implements PluginInterface
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
            new FilterSubscriber(
                $config->getSettings('filter')
            )
        );
    }
}
