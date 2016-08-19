<?php

namespace Sqon\Builder\Plugin;

use DateTime;
use DateTimeZone;
use Sqon\Builder\ConfigurationInterface;
use Sqon\SqonInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface as SchemaInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Makes Date information available for replacement.
 *
 * The Date plugin modifies the settings for the Replace plugin so that date
 * and time information can be set. The settings provided are used to create
 * and consume a new `DateTime` instance.
 *
 * ```php
 * [
 *     // Date replacement settings.
 *     'date' => [
 *
 *         // 2016-08-19T03:01:11+00:00
 *         [
 *             'pattern' => '/ISO-8601/',
 *
 *             // default:
 *             // 'format' => 'c',
 *             // 'when' => 'now',
 *             // 'zone' => 'UTC'
 *         ],
 *
 *         // August 19th, 2016 at 8:01 PM
 *         [
 *             'pattern' => '/human/',
 *             'format' => 'F jS, Y at g:i A'
 *
 *             // default:
 *             // 'when' => 'now',
 *             // 'zone' => 'UTC'
 *         ],
 *
 *         // 2016-08-20 20:01:11
 *         [
 *             'pattern' => '/future/',
 *             'format' => 'Y-m-d H:i:s',
 *             'when' => 'tomorrow'
 *
 *             // default:
 *             // 'zone' => 'UTC'
 *         ]
 *
 *     ],
 *
 *     // The corresponding replacement plugin settings.
 *     'replace' => [
 *
 *         // Replace for all paths.
 *         'all' => [
 *             '/ISO-8601/' => 'ISO 8601: %s'
 *         ],
 *
 *         // Replace for a specific path.
 *         'path' => [
 *             'src/build.php' => [
 *                 '/human/' => 'Date: %s'
 *             ]
 *         ],
 *
 *         // Replace for paths matching a pattern.
 *         'pattern' => [
 *             '/\.php$/' => [
 *                 '/future/' => '%s'
 *             ]
 *         ]
 *
 *     ]
 * ]
 * ```
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Date extends AbstractReplaceExtension implements
    PluginInterface,
    SchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $root = $tree->root('date');

        $root
            ->prototype('array')
                ->children()
                    ->scalarNode('format')
                        ->cannotBeEmpty()
                        ->defaultValue('c')
                    ->end()
                    ->scalarNode('pattern')
                        ->cannotBeEmpty()
                        ->isRequired()
                    ->end()
                    ->scalarNode('when')
                        ->cannotBeEmpty()
                        ->defaultValue('now')
                    ->end()
                    ->scalarNode('zone')
                        ->cannotBeEmpty()
                        ->defaultValue('UTC')
                    ->end()
                ->end()
            ->end()
        ;

        return $tree;
    }

    /**
     * {@inheritdoc}
     */
    public function register(
        EventDispatcherInterface $dispatcher,
        ConfigurationInterface $config,
        SqonInterface $sqon
    ) {
        $this->checkReplace($dispatcher);

        $replace = $config->getSettings('replace');

        // @codeCoverageIgnoreStart
        if (empty($replace)) {
            return;
        }
        // @codeCoverageIgnoreEnd

        foreach ($config->getSettings('date') as $pattern) {
            $dateTime = new DateTime(
                $pattern['when'],
                new DateTimeZone($pattern['zone'])
            );

            $this->replaceSettings(
                $replace,
                [$pattern['pattern']],
                $dateTime->format($pattern['format'])
            );
        }

        $config->setSettings('replace', $replace);
    }
}
