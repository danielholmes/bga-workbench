<?php

namespace BGAWorkbench\Console;

use BGAWorkbench\External\WorkbenchProjectConfigSerialiser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class InitCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Creates a configuration file for a BGA project');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = new \SplFileInfo(getcwd());
        if (WorkbenchProjectConfigSerialiser::configExists($directory)) {
            $output->writeln('<error>Config file is already present</error>');
            return 1;
        }

        $raw = $this->deployConfig(
            $input,
            $output,
            $this->testDbConfig(
                $input,
                $output,
                $this->useComposerConfig(
                    $input,
                    $output,
                    []
                )
            )
        );
        WorkbenchProjectConfigSerialiser::writeToDirectory($directory, $raw);

        return 0;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array $config
     * @return array
     */
    private function useComposerConfig(InputInterface $input, OutputInterface $output, array $config) : array
    {
        $question = new ChoiceQuestion(
            'Is this project using composer for dependencies? (if you don\'t know what composer is then choose no)',
            ['n' => 'no', 'y' => 'yes'],
            'n'
        );
        $useComposer = $this->getHelper('question')->ask($input, $output, $question) === 'y';

        return array_merge($config, ['useComposer' => $useComposer]);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array $config
     * @return array
     */
    private function testDbConfig(InputInterface $input, OutputInterface $output, array $config) : array
    {
        $helper = $this->getHelper('question');

        $namePrefix = $helper->ask($input, $output, new Question('Test database prefix: '));
        $user = $helper->ask($input, $output, new Question('Test database user: '));
        $pass = $helper->ask($input, $output, new Question('Test database password: '));

        return array_merge(
            $config,
            [
                'testDb' => [
                    'namePrefix' => $namePrefix,
                    'user' => $user,
                    'pass' => $pass
                ]
            ]
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array $config
     * @return array
     */
    private function deployConfig(InputInterface $input, OutputInterface $output, array $config) : array
    {
        $helper = $this->getHelper('question');

        $host = $helper->ask($input, $output, new Question('Sftp host: '));
        $user = $helper->ask($input, $output, new Question('Sftp user: '));
        $pass = $helper->ask($input, $output, new Question('Sftp password: '));

        return array_merge(
            $config,
            [
                'sftp' => [
                    'host' => $host,
                    'user' => $user,
                    'pass' => $pass
                ]
            ]
        );
    }
}
