<?php

namespace BGAWorkbench\Tests;

use BGAWorkbench\Project\Project;
use PhpOption\Some;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;
use Functional as F;

class ProjectTest extends TestCase
{
    /**
     * @var Project
     */
    private $project;

    protected function setUp()
    {
        $this->project = new Project(
            new \SplFileInfo(realpath(__DIR__ . '/../../..')),
            'battleforhill',
            []
        );
    }

    public function testBuildInputPaths()
    {
        // TODO: Need a test project
        assertThat(
            $this->project->getBuildInputPaths(),
            containsInAnyOrder(
                F\map(
                    [
                        'img',
                        'battleforhill.css',
                        'battleforhill.js',
                        'battleforhill.game.php',
                        'battleforhill.action.php',
                        'battleforhill.view.php',
                        'battleforhill_battleforhill.tpl',
                        'states.inc.php',
                        'stats.inc.php',
                        'material.inc.php',
                        'gameoptions.inc.php',
                        'gameinfos.inc.php',
                        'dbmodel.sql',
                        'version.php'
                    ],
                    function ($path) {
                        return $this->project->absoluteToProjectRelativeFile(
                            new \SplFileInfo(
                                $this->project->getDirectory()->getPathname() . DIRECTORY_SEPARATOR . $path
                            )
                        );
                    }
                )
            )
        );
    }

    public function testRequiredFiles()
    {
        assertThat(
            $this->project->getRequiredFiles(),
            containsInAnyOrder(
                F\map(
                    [
                        'img/game_box.png',
                        'img/game_box180.png',
                        'img/game_box75.png',
                        'img/game_icon.png',
                        'img/publisher.png',
                        'battleforhill.css',
                        'battleforhill.js',
                        'battleforhill.game.php',
                        'battleforhill.action.php',
                        'battleforhill.view.php',
                        'battleforhill_battleforhill.tpl',
                        'states.inc.php',
                        'stats.inc.php',
                        'material.inc.php',
                        'gameoptions.inc.php',
                        'gameinfos.inc.php',
                        'dbmodel.sql',
                        'version.php'
                    ],
                    function ($path) {
                        return $this->project->absoluteToProjectRelativeFile(
                            new \SplFileInfo(
                                $this->project->getDirectory()->getPathname() . DIRECTORY_SEPARATOR . $path
                            )
                        );
                    }
                )
            )
        );
    }

    public function testGetFileVariableValue()
    {
        assertThat(
            $this->project->getFileVariableValue('version.php', 'game_version_battleforhill'),
            equalTo(new Some('999999-9999'))
        );
    }

    public function testAbsoluteToProjectRelativeFile()
    {
        $fullPath = join(DIRECTORY_SEPARATOR, [$this->project->getDirectory()->getPathname(), 'img', 'cards.png']);
        $versionFile = new \SplFileInfo($fullPath);
        assertThat(
            $this->project->absoluteToProjectRelativeFile($versionFile),
            equalTo(new SplFileInfo($fullPath, 'img', 'img' . DIRECTORY_SEPARATOR . 'cards.png'))
        );
    }

    public function testAbsoluteToProjectRelativeFileInvalid()
    {
        $this->expectException('InvalidArgumentException');

        $tempDir = new \SplFileInfo(sys_get_temp_dir());
        $this->project->absoluteToProjectRelativeFile($tempDir);
    }
}
