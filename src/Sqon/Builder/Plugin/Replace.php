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
        $subscriber = new ReplaceSubscriber();

        foreach ($config->getSettings('replace') as $type => $sets) {
            switch ($type) {
                case 'all':
                    foreach ($sets as $pattern => $replacement) {
                        $subscriber->replaceAll($pattern, $replacement);
                    }

                    break;

                case 'path':
                    foreach ($sets as $path => $replacements) {
                        foreach ($replacements as $pattern => $replacement) {
                            $subscriber->replaceByPath(
                                $path,
                                $pattern,
                                $replacement
                            );
                        }
                    }

                    break;

                case 'pattern':
                    foreach ($sets as $path => $replacements) {
                        foreach ($replacements as $pattern => $replacement) {
                            $subscriber->replaceByPattern(
                                $path,
                                $pattern,
                                $replacement
                            );
                        }
                    }

                    break;
            }
        }

        $dispatcher->addSubscriber($subscriber);
    }
}
