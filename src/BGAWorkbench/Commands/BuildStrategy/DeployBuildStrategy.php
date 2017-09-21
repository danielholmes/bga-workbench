<?php

namespace BGAWorkbench\Commands\BuildStrategy;

use BGAWorkbench\ProductionDeployment;
use BGAWorkbench\Project\DeployConfig;
use BGAWorkbench\Project\Project;
use Illuminate\Filesystem\Filesystem;
use PhpOption\Option;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;

class DeployBuildStrategy implements BuildStrategy
{
    /**
     * @var BuildStrategy
     */
    private $beforeStrategy;

    /**
     * @var Project
     */
    private $project;

    /**
     * @var ProductionDeployment
     */
    private $deployment;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @param BuildStrategy $beforeStrategy
     * @param DeployConfig $deployConfig
     * @param Project $project
     */
    public function __construct(BuildStrategy $beforeStrategy, DeployConfig $deployConfig, Project $project)
    {
        $this->fileSystem = new Filesystem();
        $this->beforeStrategy = $beforeStrategy;
        $this->deployment = new ProductionDeployment(
            $deployConfig->getHost(),
            $deployConfig->getUsername(),
            $deployConfig->getPassword(),
            $project->getName()
        );
        $this->project = $project;
    }

    private function ensureDeploymentConnected()
    {
        if ($this->deployment->isConnected()) {
            return;
        }
        $this->deployment->connect();
    }

    /**
     * @inheritdoc
     */
    public function run(OutputInterface $output, Option $changedFiles)
    {
        $beforeChangedFiles = $this->beforeStrategy->run($output, $changedFiles);
        $this->ensureDeploymentConnected();

        $outputCallback = function ($num, $total, SplFileInfo $file) use ($output) {
            $output->writeln("{$num}/{$total} -> {$file->getRelativePathname()}");
        };
        if (empty($beforeChangedFiles)) {
            $allFiles = $this->fileSystem->allFiles($this->project->getDistDirectory()->getPathname());
            $this->deployment->deployChangedFiles($allFiles, $outputCallback);
        } else {
            $this->deployment->deployFiles($beforeChangedFiles, $outputCallback);
        }

        return $beforeChangedFiles;
    }
}
