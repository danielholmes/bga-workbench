<?php

namespace BGAWorkbench\Commands;

use BGAWorkbench\Project\WorkbenchProjectConfig;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('clean')
            ->setDescription('Cleans project by removing intermediate build steps');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = WorkbenchProjectConfig::loadFromCwd();
        $project = $config->loadProject();

        $fileSystem = new Filesystem();
        $fileSystem->deleteDirectory($project->getBuildDirectory());
        $fileSystem->deleteDirectory($project->getDistDirectory());

        $output->writeln('<info>Builds deleted</info>');
    }
}
