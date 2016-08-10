<?php

namespace Sqon;

/**
 * Returns the canonicalized path.
 *
 * This function will canonicalize a path by:
 *
 * - removing empty path segments (e.g. `//`(+) -> `/`)
 * - removing "." from the path
 * - removing ".." and the preceding path segment from the path
 *
 * @param string $path The path to canonicalize.
 *
 * @return string The canonicalized path.
 */
function canonicalize($path)
{
    $canon = [];
    $parts = preg_split('{[\\\/]+}', $path);
    $start = null;

    if (!empty($parts) && preg_match('/^([a-zA-Z]:|)$/', $parts[0])) {
        $start = array_shift($parts);
    }

    foreach ($parts as $part) {
        if ('.' === $part) {
            continue;
        }

        if ('..' === $part) {
            array_pop($canon);
        } else {
            $canon[] = $part;
        }
    }

    if (null !== $start) {
        $canon = array_merge([$start], $canon);
    }

    return join(DIRECTORY_SEPARATOR, $canon);
}

/**
 * Checks if a path is relative.
 *
 * This function determines a path is relative if:
 *
 * - The path is a fully qualified Windows path (e.g. "C:\path\to").
 * - The path is a fully qualified Unix path (e.g. "/path/to").
 *
 * @param string $path The path to check.
 *
 * @return boolean Returns `true` if relative, `false` if not.
 */
function is_relative($path)
{
    return !(bool) preg_match('/^([a-zA-z]:)?[\\\\\\/]/', $path);
}
