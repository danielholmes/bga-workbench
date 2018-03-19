<?php

namespace BGAWorkbench\Tests\Console;

use BGAWorkbench\Console\Application;
use BGAWorkbench\External\WorkbenchProjectConfigSerialiser;
use BGAWorkbench\Project\DeployConfig;
use BGAWorkbench\Project\WorkbenchProjectConfig;
use BGAWorkbench\TestUtils\WorkingDirectory;
use PhpOption\Some;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class InitCommandTest extends TestCase
{
    /**
     * @var WorkingDirectory
     */
    private $workingDir;

    /**
     * @var CommandTester
     */
    private $tester;

    protected function setUp()
    {
        $this->workingDir = WorkingDirectory::createTemp();
        chdir($this->workingDir->getPathname());

        $application = new Application();
        $application->setAutoExit(false);
        $command = $application->find('init');

        $this->tester = new CommandTester($command);
    }

    public function testSuccess()
    {
        $this->tester->setInputs([
            'y',

            'dbname',
            'dbuser',
            'dbpass',

            'shost',
            'suser',
            'spass'
        ]);
        $this->tester->execute([]);

        assertThat($this->tester->getStatusCode(), equalTo(0));
        assertThat(
            WorkbenchProjectConfigSerialiser::readFromDirectory($this->workingDir->getFileInfo()),
            equalTo(
                new WorkbenchProjectConfig(
                    $this->workingDir->getFileInfo(),
                    true,
                    [],
                    'dbname',
                    'dbuser',
                    'dbpass',
                    'php',
                    new Some(
                        new DeployConfig('shost', 'suser', 'spass')
                    )
                )
            )
        );
    }

    public function testAlreadyExists()
    {
        touch(WorkbenchProjectConfigSerialiser::getConfigFileInfo($this->workingDir->getFileInfo()));

        $this->tester->execute([]);

        assertThat($this->tester->getStatusCode(), not(equalTo(0)));
    }
}
