<?php

namespace Sqon\Builder\Plugin;

use Sqon\Builder\Exception\Builder\PluginException;
use Sqon\Event\BeforeSetPathEvent;
use Sqon\Event\Subscriber\ReplaceSubscriber;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Provides support for manipulating the Replace plugin settings.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
abstract class AbstractReplaceExtension
{
    /**
     * Throws an exception if the replace subscriber is already registered.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     *
     * @throws PluginException If the subscriber could not be found.
     */
    protected function checkReplace(EventDispatcherInterface $dispatcher)
    {
        $listeners = $dispatcher->getListeners(BeforeSetPathEvent::NAME);

        foreach ((array) $listeners as $listener) {
            // @codeCoverageIgnoreStart
            if ($listener[0] instanceof ReplaceSubscriber) {
                throw new PluginException(
                    'The Replace plugin must be registered after Git.'
                );
            }
            // @codeCoverageIgnoreEnd
        }
    }


    /**
     * Selectively replaces the values of certain replace patterns.
     *
     * The value of a pattern is replaced using `sprintf()`. A single `%s` is
     * required to be in the value. For example, if the pattern `/example/` is
     * being replaced with `value`, then `value %s` is expected (or something
     * similar).
     *
     * @param array  &$settings The new settings for the Replace plugin.
     * @param array  $patterns  The patterns to replace.
     * @param string $value     The value to replace with.
     */
    protected function replaceSettings(
        array &$settings,
        array $patterns,
        $value
    ) {
        $set = function ($key, $pattern, $value) use (&$settings) {
            foreach ($settings[$key] as &$set) {
                if ($pattern === $set['pattern']) {
                    $set['replacement'] = sprintf(
                        $set['replacement'],
                        $value
                    );
                }
            }
        };

        foreach ($patterns as $pattern) {
            $set('all', $pattern, $value);
            $set('path', $pattern, $value);
            $set('pattern', $pattern, $value);
        }
    }
}
