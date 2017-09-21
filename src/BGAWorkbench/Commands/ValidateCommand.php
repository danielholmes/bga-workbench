<?php

namespace BGAWorkbench\Commands;

use BGAWorkbench\Project\Project;
use BGAWorkbench\Project\WorkbenchProjectConfig;
use BGAWorkbench\Validate\StateConfiguration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Functional as F;

class ValidateCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('validate')
            ->setDescription('Validates that the BGA project is valid')
            ->setHelp(<<<HELP
        Runs various checks on the BGA project such as valid state configuration, valid php syntax and all required 
        files
HELP
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = WorkbenchProjectConfig::loadFromCwd();
        $project = $config->loadProject();

        $this->validateRequiredFilesExist($project);
        $this->validateFilesPhp($config, $project, $output);
        $this->validateStates($project);

        $output->writeln('<info>All validation checks passed</info>');
    }

    /**
     * @param Project $project
     */
    private function validateRequiredFilesExist(Project $project)
    {
        $notFoundList = join(
            ', ',
            F\map(
                F\sort(
                    F\filter(
                        $project->getRequiredFiles(),
                        function (SplFileInfo $file) {
                            return !$file->isFile();
                        }
                    ),
                    function (SplFileInfo $file) {
                        return $file->getPathname();
                    }
                ),
                function (SplFileInfo $file) {
                    return $file->getRelativePathname();
                }
            )
        );
        if (!empty($notFoundList)) {
            throw new \RuntimeException("Missing required files: {$notFoundList}");
        }
    }

    /**
     * @param WorkbenchProjectConfig $config
     * @param Project $project
     * @param OutputInterface $output
     */
    private function validateFilesPhp(WorkbenchProjectConfig $config, Project $project, OutputInterface $output)
    {
        $processes = F\map(
            $project->getDevelopmentPhpFiles(),
            function (SplFileInfo $file) use ($config) {
                return ProcessBuilder::create([$config->getLinterPhpBin(), '-l', $file->getPathname()])
                    ->getProcess();
            }
        );
        F\each(
            $processes,
            function (Process $process) {
                $process->run();
            }
        );
        $invalid = F\map(
            F\filter(
                $processes,
                function (Process $process) {
                    return !$process->isSuccessful();
                }
            ),
            function (Process $process) {
                return $process->getOutput();
            }
        );
        if (count($invalid) > 0) {
            throw new \RuntimeException(join(', ', $invalid));
        }
    }

    /**
     * @param Project $project
     */
    private function validateStates(Project $project)
    {
        require_once(__DIR__ . '/../Stubs/framework.php');
        $variableName = 'machinestates';
        $fileName = 'states.inc.php';
        $states = $project->getFileVariableValue($fileName, $variableName)
            ->getOrThrow(new \RuntimeException("Expect variable {$variableName} in {$fileName}"));

        $processor = new Processor();
        $validated = $processor->processConfiguration(new StateConfiguration(), [$states]);
        $stateIds = array_keys($states);

        F\each(
            $validated,
            function (array $state) use ($stateIds) {
                if (!isset($state['transitions'])) {
                    return;
                }

                $transitionToIds = array_values($state['transitions']);
                $differentIds = array_diff($transitionToIds, $stateIds);
                if (!empty($differentIds)) {
                    $missingList = join(', ', $differentIds);
                    $allList = join(', ', $stateIds);
                    throw new \RuntimeException(
                        "State {$state['name']} has transition to missing state ids {$missingList} / ({$allList})"
                    );
                }
            }
        );
    }
}
