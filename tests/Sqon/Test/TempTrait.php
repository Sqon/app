<?php

namespace Test\Sqon\Test;

/**
 * Manages temporary file and directory paths.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
trait TempTrait
{
    /**
     * The temporary paths.
     *
     * @var string[]
     */
    private $tempPaths = [];

    /**
     * Creates a new temporary directory.
     */
    public function createTempDirectory()
    {
        unlink($file = $this->createTempFile());
        mkdir($file);

        return $file;
    }

    /**
     * Creates a new temporary file path.
     */
    public function createTempFile()
    {
        return $this->tempPaths[] = tempnam(sys_get_temp_dir(), 'sqon-');
    }

    /**
     * Recursively deletes a path.
     *
     * @param string $path The path to delete.
     */
    public function deletePath($path)
    {
        if (is_dir($path)) {
            foreach (scandir($path) as $file) {
                if (('.' === $file) || ('..' === $file)) {
                    continue;
                }

                $this->deletePath($path . DIRECTORY_SEPARATOR . $file);
            }

            rmdir($path);
        } elseif (is_file($path)) {
            unlink($path);
        }
    }

    /**
     * Deletes the temporary paths.
     */
    public function deleteTempPaths()
    {
        foreach ($this->tempPaths as $path) {
            $this->deletePath($path);
        }

        $this->tempPaths = [];
    }
}
