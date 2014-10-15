<?php

class TestCase extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
    }

    /**
     * Copy source directory to destination directory.
     * Destination must not exist.
     *
     * @param string $source the directory source
     * @param string $dest   the destination
     *
     * @return void
     */
    protected static function copyDir($source , $dest)
    {
        if ( file_exists( $dest ) ) die("Unable to create directory '$dest', already exists!");
        mkdir( $dest, 0755 );
        foreach (
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST) as $item) {
            if ($item->isDir()) {
                mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            } else {
                copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
        }
    }

    /**
    * Recursively delete a directory
    *
    * @param string $dir Directory name
    * @param boolean $deleteRootToo Delete specified top-level directory as well
    */
    public static function unlinkRecursive($dir, $deleteRootToo = true)
    {
        if (!$dh = @opendir($dir)) {
            return;
        }
        while (false !== ($obj = readdir($dh))) {
            if ($obj == '.' || $obj == '..') {
                continue;
            }

            if (!@unlink($dir . '/' . $obj)) {
                self::unlinkRecursive($dir.'/'.$obj, true);
            }
        }
        closedir($dh);
        if ($deleteRootToo) {
            @rmdir($dir);
        }

        return;
    }

}
