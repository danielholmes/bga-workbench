<?php

namespace BGAWorkbench\Builder;

use Symfony\Component\Finder\SplFileInfo;

interface BuildInstruction
{
    /**
     * @return \SplFileInfo[]
     */
    public function getInputPaths() : array;

    /**
     * @param \SplFileInfo $distDir
     * @param SplFileInfo[] $changedFiles
     * @return \SplFileInfo[]
     */
    public function runWithChanged(\SplFileInfo $distDir, array $changedFiles) : array;

    /**
     * @param \SplFileInfo $distDir
     * @return \SplFileInfo[]
     */
    public function run(\SplFileInfo $distDir) : array;
}
