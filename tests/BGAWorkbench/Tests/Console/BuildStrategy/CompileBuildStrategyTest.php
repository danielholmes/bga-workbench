<?php

namespace BGAWorkbench\Tests\Console\BuildStrategy;

use BGAWorkbench\Console\BuildStrategy\CompileBuildStrategy;
use BGAWorkbench\Test\Fixtures;
use Illuminate\Filesystem\Filesystem;
use PhpOption\None;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\NullOutput;

class CompileBuildStrategyTest extends TestCase
{
    public function testRunWithDepsWritesCorrectOrder()
    {
        $fileSystem = new Filesystem();
        $project = Fixtures::loadTestProject('simple-composer-example');
        $fileSystem->deleteDirectory($project->getDistDirectory());
        $compile = new CompileBuildStrategy($project->getBuildInstructions(), $project->getDistDirectory());

        $compile->run(new NullOutput(), None::create());

        $distGamePath = $project->getDistDirectory()->getPathname() . '/' . $project->getGameProjectFileRelativePathname();
        $gameContents = @file_get_contents($distGamePath);

        $optionPos = strpos($gameContents, 'class Option');
        $somePos = strpos($gameContents, 'class Some');
        $nonePos = strpos($gameContents, 'class None');
        $gamePos = strpos($gameContents, 'class Example extends Table');
        assertThat($optionPos, lessThan($somePos));
        assertThat($optionPos, lessThan($nonePos));
        assertThat($somePos, lessThan($gamePos));
        assertThat($nonePos, lessThan($gamePos));
    }
}
