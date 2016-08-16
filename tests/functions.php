<?php

/*
 * This script contains support functions for test cases.
 */

use Composer\Autoload\ClassLoader;

/**
 * A mock of the get_composer_autoloader() function.
 *
 * @return ClassLoader The Composer autoloader.
 */
function get_composer_autoloader()
{
    static $loader;

    if (null === $loader) {
        $loader = new ClassLoader();
    }

    return $loader;
}
