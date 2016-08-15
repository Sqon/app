<?php

namespace Test\Sqon;

use PHPUnit_Framework_TestCase as TestCase;

use function Sqon\canonicalize;
use function Sqon\is_relative;

/**
 * Verifies that the Sqon utility functions work as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class bootstrapTest extends TestCase
{
    /**
     * Returns the absolute paths.
     *
     * @return string[] The paths.
     */
    public function getAbsolutePaths()
    {
        return [
            ['C:\example\path'],
            ['/example/path']
        ];
    }

    /**
     * Returns the canonicalized paths.
     *
     * @return string[] The paths.
     */
    public function getCanonicalizedPaths()
    {
        $d = DIRECTORY_SEPARATOR;

        return [
            ["C:\\path\\to\\\\something", "C:${d}path${d}to${d}something"],
            ["C:\\path\\to\\.\\something", "C:${d}path${d}to${d}something"],
            ["C:\\path\\to\\..\\something", "C:${d}path${d}something"],
            ["C:\\path\\to\\..\\..\\..\\something", "C:{$d}something"],

            ["/path/to//something", "${d}path${d}to${d}something"],
            ["/path/to/./something", "${d}path${d}to${d}something"],
            ["/path/to/../something", "${d}path${d}something"],
            ["/path/to/../../../something", "${d}something"]
        ];
    }

    /**
     * Returns the relative paths.
     *
     * @return string[] The paths.
     */
    public function getRelativePaths()
    {
        return [
            ['example'],
            ['example/path'],
            ['example\path']
        ];
    }

    /**
     * Verify that a path can be canonicalized.
     *
     * @dataProvider getCanonicalizedPaths
     *
     * @param string $original  The original path.
     * @param string $canonical The canonicalized path.
     */
    public function testPathCanBeCanonicalized($original, $canonical)
    {
        self::assertEquals(
            $canonical,
            canonicalize($original),
            'The path was not canonicalized properly.'
        );
    }

    /**
     * Verify that a path can be checked for relativity.
     *
     * @dataProvider getAbsolutePaths
     *
     * @param string $path The path to check.
     */
    public function testCheckIfPathIsAbsolute($path)
    {
        self::assertFalse(
            is_relative($path),
            'The path should be absolute.'
        );
    }

    /**
     * Verify that a path can be checked for relativity.
     *
     * @dataProvider getRelativePaths
     *
     * @param string $path The path to check.
     */
    public function testCheckIfPathIsRelative($path)
    {
        self::assertTrue(
            is_relative($path),
            'The path should be relative.'
        );
    }
}
