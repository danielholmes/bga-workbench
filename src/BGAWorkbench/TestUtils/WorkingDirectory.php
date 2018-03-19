<?php

namespace BGAWorkbench\TestUtils;

class WorkingDirectory
{
    /**
     * @return WorkingDirectory
     */
    public static function createTemp()
    {
        $directory = new \SplFileInfo(tempnam(sys_get_temp_dir(), 'bgawb-test-'));
        unlink($directory->getPathname());
        mkdir($directory->getPathname(), 0777, true);
        return new WorkingDirectory($directory);
    }

    /**
     * @var \SplFileInfo
     */
    private $directory;

    /**
     * @param \SplFileInfo $directory
     */
    public function __construct(\SplFileInfo $directory)
    {
        $this->directory = $directory;
    }

    /**
     * @return \SplFileInfo
     */
    public function getFileInfo()
    {
        return $this->directory;
    }

    /**
     * @return string
     */
    public function getPathname()
    {
        return $this->directory->getPathname();
    }

    public function __destruct()
    {
        if ($this->directory->isDir()) {
            @rmdir($this->directory->getPathname());
        }
    }
}
