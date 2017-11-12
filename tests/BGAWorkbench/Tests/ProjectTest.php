<?php

namespace BGAWorkbench\Tests;

use BGAWorkbench\Project\Project;
use BGAWorkbench\Test\Fixtures;
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
        $this->project = Fixtures::loadTestProject('simple-example');
    }

    public function testBuildInputPaths()
    {
        assertThat(
            $this->project->getBuildInputPaths(),
            containsInAnyOrder(
                F\map(
                    [
                        'img',
                        'example.css',
                        'example.js',
                        'example.game.php',
                        'example.action.php',
                        'example.view.php',
                        'example_example.tpl',
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
                        'img/game_icon.png',
                        'img/publisher.png',
                        'example.css',
                        'example.js',
                        'example.game.php',
                        'example.action.php',
                        'example.view.php',
                        'example_example.tpl',
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
            $this->project->getFileVariableValue('version.php', 'game_version_example'),
            equalTo(new Some('999999-9999'))
        );
    }

    public function testAbsoluteToProjectRelativeFile()
    {
        $fullPath = join(
            DIRECTORY_SEPARATOR,
            [$this->project->getDirectory()->getPathname(), 'img', 'game_box.png']
        );
        $boxFile = new \SplFileInfo($fullPath);
        assertThat(
            $this->project->absoluteToProjectRelativeFile($boxFile),
            equalTo(
                new SplFileInfo(
                    $fullPath,
                    'img',
                    'img' . DIRECTORY_SEPARATOR . 'game_box.png')
            )
        );
    }

    public function testAbsoluteToProjectRelativeFileInvalid()
    {
        $this->expectException('InvalidArgumentException');

        $tempDir = new \SplFileInfo(sys_get_temp_dir());
        $this->project->absoluteToProjectRelativeFile($tempDir);
    }
}
