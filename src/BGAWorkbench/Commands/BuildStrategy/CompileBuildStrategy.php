<?php

namespace BGAWorkbench\Commands\BuildStrategy;

use BGAWorkbench\Builder\BuildInstruction;
use Functional as F;
use PhpOption\Option;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;

class CompileBuildStrategy implements BuildStrategy
{
    /**
     * @var SplFileInfo
     */
    private $distDir;

    /**
     * @var BuildInstruction[]
     */
    private $buildInstructions;

    /**
     * @param BuildInstruction[] $buildInstructions
     * @param SplFileInfo $distDir
     */
    public function __construct(array $buildInstructions, SplFileInfo $distDir)
    {
        $this->buildInstructions = $buildInstructions;
        $this->distDir = $distDir;
    }

    /**
     * @inheritdoc
     */
    public function run(OutputInterface $output, Option $changedFiles)
    {
        return F\unique(
            F\flat_map(
                $this->buildInstructions,
                function (BuildInstruction $instruction) use ($changedFiles) {
                    if ($changedFiles->isEmpty()) {
                        return $instruction->run($this->distDir);
                    }

                    $matchingChangedFiles = $this->getMatchingFiles($instruction, $changedFiles->get());
                    if (empty($matchingChangedFiles)) {
                        return [];
                    }

                    return $instruction->runWithChanged($this->distDir, $matchingChangedFiles);
                }
            )
        );
    }

    /**
     * @param BuildInstruction $instruction
     * @param SplFileInfo[] $changedFiles
     * @return SplFileInfo[]
     */
    private function getMatchingFiles(BuildInstruction $instruction, array $changedFiles) : array
    {
        return array_values(
            F\filter(
                $changedFiles,
                function (SplFileInfo $file) use ($instruction) {
                    return $this->doesFileMatchInstruction($instruction, $file);
                }
            )
        );
    }

    /**
     * @param BuildInstruction $instruction
     * @param SplFileInfo $file
     * @return bool
     */
    private function doesFileMatchInstruction(BuildInstruction $instruction, SplFileInfo $file) : bool
    {
        return F\some(
            $instruction->getInputPaths(),
            function (\SplFileInfo $matcher) use ($file) {
                if ($matcher->isDir()) {
                    return strpos($file->getRealPath(), $matcher->getRealPath()) === 0;
                }

                return $matcher->getRealPath() === $file->getRealPath();
            }
        );
    }
}
