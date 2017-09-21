<?php

namespace BGAWorkbench\Builder;

use BGAWorkbench\Utils\FileUtils;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;
use Functional as F;

class CopyFiles implements BuildInstruction
{
    /**
     * @var SplFileInfo
     */
    private $path;

    /**
     * @var \SplFileInfo
     */
    private $projectDir;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @param Filesystem $fileSystem
     * @param SplFileInfo $path
     */
    public function __construct(Filesystem $fileSystem, SplFileInfo $path)
    {
        $this->fileSystem = $fileSystem;
        $this->projectDir = new \SplFileInfo(str_replace_first($path->getRelativePathname(), '', $path->getPathname()));
        $this->path = $path;
    }

    /**
     * @inheritdoc
     */
    public function getInputPaths() : array
    {
        return [$this->path];
    }

    /**
     * @inheritdoc
     */
    public function runWithChanged(\SplFileInfo $distDir, array $changedFiles) : array
    {
        return F\flat_map(
            $changedFiles,
            function (SplFileInfo $file) use ($distDir) {
                $dest = FileUtils::createRelativeFileFromSubPath($distDir, $file->getRelativePathname());
                if ($this->fileSystem->exists($dest->getPathname()) && $dest->getMTime() >= $file->getMTime()) {
                    return [];
                }

                $this->fileSystem->makeDirectory($dest->getPath(), 0755, true, true);
                $this->fileSystem->copy($file->getPathname(), $dest->getPathname());
                return [$dest];
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function run(\SplFileInfo $distDir) : array
    {
        return $this->runWithChanged($distDir, $this->getAllFiles());
    }

    /**
     * @return SplFileInfo[]
     */
    private function getAllFiles() : array
    {
        if ($this->path->isFile()) {
            return [$this->path];
        }

        return F\map(
            $this->fileSystem->allFiles($this->path->getPathname()),
            function (SplFileInfo $file) {
                return FileUtils::createRelativeFileFromExisting($this->projectDir, $file);
            }
        );
    }
}
