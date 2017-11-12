<?php

namespace BGAWorkbench\Utils;

use Symfony\Component\Finder\SplFileInfo;

class FileUtils
{
    /**
     * @param \SplFileInfo $directory
     * @param \SplFileInfo $file
     * @return SplFileInfo
     */
    public static function createRelativeFileFromExisting(\SplFileInfo $directory, \SplFileInfo $file) : SplFileInfo
    {
        if (strpos($file->getRealPath(), $directory->getRealPath()) !== 0) {
            throw new \InvalidArgumentException("File {$file} not within {$directory}");
        }

        $relativePathname = str_replace_first(
            $directory->getPathname() . DIRECTORY_SEPARATOR,
            '',
            $file->getPathname()
        );
        return new SplFileInfo($file->getPathname(), dirname($relativePathname), $relativePathname);
    }

    /**
     * @param \SplFileInfo $directory
     * @param string $subPath
     * @return SplFileInfo
     */
    public static function createRelativeFileFromSubPath(\SplFileInfo $directory, $subPath) : SplFileInfo
    {
        return new SplFileInfo(self::joinPath($directory, $subPath), dirname($subPath), $subPath);
    }

    /**
     * @param string ...$part
     * @return string
     */
    public static function joinPath()
    {
        $parts = func_get_args();
        if (empty($parts)) {
            throw new \InvalidArgumentException('Must provide some parts');
        }
        return join(DIRECTORY_SEPARATOR, $parts);
    }

    /**
     * @param string ...$part
     * @return \SplFileInfo
     */
    public static function joinPathToFileInfo()
    {
        return new \SplFileInfo(call_user_func_array(['self', 'joinPath'], func_get_args()));
    }
}
