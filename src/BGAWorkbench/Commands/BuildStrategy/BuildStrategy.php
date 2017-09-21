<?php

namespace BGAWorkbench\Commands\BuildStrategy;

use PhpOption\Option;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;

interface BuildStrategy
{
    /**
     * @param OutputInterface $output
     * @param Option $changedFiles
     * @return SplFileInfo[]
     */
    public function run(OutputInterface $output, Option $changedFiles);
}
