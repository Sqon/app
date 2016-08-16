<?php

namespace Sqon\Builder\Plugin;

use Sqon\Builder\ConfigurationInterface;
use Sqon\Event\Subscriber\ReplaceSubscriber;
use Sqon\SqonInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Registers the `ReplaceSubscriber` as a Sqon builder plugin.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Replace implements PluginInterface
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
            new ReplaceSubscriber(
                $config->getSettings('replace')
            )
        );
    }
}
