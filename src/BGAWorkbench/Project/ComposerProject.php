<?php

namespace BGAWorkbench\Project;

use BGAWorkbench\Builder\CompileComposerGame;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;
use Functional as F;

class ComposerProject extends Project
{
    /**
     * @var string[]
     */
    private $extraSrcPaths;

    /**
     * @param \SplFileInfo $directory
     * @param string $name
     * @param string[] $extraSrcPaths
     */
    public function __construct(\SplFileInfo $directory, string $name, array $extraSrcPaths)
    {
        parent::__construct($directory, $name);
        $this->extraSrcPaths = $extraSrcPaths;
    }

    /**
     * @inheritdoc
     */
    protected function createGameProjectFileBuildInstruction(Filesystem $fileSystem, SplFileInfo $file)
    {
        return new CompileComposerGame(
            new Filesystem(),
            $this->getBuildDirectory(),
            $this->getProjectFile('composer.json'),
            $this->getProjectFile('composer.lock'),
            $file,
            F\map(
                $this->extraSrcPaths,
                function ($path) {
                    return $this->getProjectFile($path);
                }
            )
        );
    }
}
