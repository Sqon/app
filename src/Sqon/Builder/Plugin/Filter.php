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
        $subscriber = new FilterSubscriber();

        foreach ($config->getSettings('filter') as $mode => $rules) {
            switch ($mode) {
                case 'exclude':
                    foreach ($rules as $type => $matches) {
                        switch ($type) {
                            case 'name':
                                foreach ($matches as $name) {
                                    $subscriber->excludeByName($name);
                                }

                                break;

                            case 'path':
                                foreach ($matches as $path) {
                                    $subscriber->excludeByPath($path);
                                }

                                break;

                            case 'pattern':
                                foreach ($matches as $pattern) {
                                    $subscriber->excludeByPattern($pattern);
                                }

                                break;
                        }
                    }

                    break;

                case 'include':
                    foreach ($rules as $type => $matches) {
                        switch ($type) {
                            case 'name':
                                foreach ($matches as $name) {
                                    $subscriber->includeByName($name);
                                }

                                break;

                            case 'path':
                                foreach ($matches as $path) {
                                    $subscriber->includeByPath($path);
                                }

                                break;

                            case 'pattern':
                                foreach ($matches as $pattern) {
                                    $subscriber->includeByPattern($pattern);
                                }

                                break;
                        }
                    }

                    break;
            }
        }

        $dispatcher->addSubscriber($subscriber);
    }
}
