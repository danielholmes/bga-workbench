<?php
namespace BGAWorkbench\Tests\Validate;

use BGAWorkbench\Project\Project;
use BGAWorkbench\TestUtils\WorkingDirectory;
use BGAWorkbench\Validate\StateValidator;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class StateValidatorTest extends TestCase
{
    /**
     * Verifies that a valid states.inc.php file passes validations
     * @doesNotPerformAssertions
     */
    public function testValid(): void
    {
        $statesIncPhpContents = <<<END
<?php
\$machinestates = array(
    1 => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array("" => 10)
    ),
    10 => array(
        "name" => "foo",
        "description" => "",
        "type" => "game",
        "action" => "stStuff",
        "transitions" => array("bar" => 11)
    ),
    11 => array(
        "name" => "bar",
        "description" => "",
        "type" => "game",
        "action" => "stBar",
        "transitions" => array("repeat" => 10, "continue" => 99)
    ),
    99 => array(
        "name" => "gameEnd",
        "description" => "End of game",
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )
);
END;

        $projectDirectory = $this->createTestProject($statesIncPhpContents);
        $project = new Project($projectDirectory->getFileInfo(), 'valid');
        (new StateValidator())->validateStates($project);
    }

    public function testMissingGameSetupState(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('stats.inc.php is missing gameSetup (1) state.');

        $statesIncPhpContents = <<<END
<?php
\$machinestates = array(
    // Intentionally missing gameSetup
    10 => array(
        "name" => "stuff",
        "description" => "",
        "type" => "game",
        "action" => "stStuff",
        "transitions" => array("continue" => 99)
    ),
    99 => array(
        "name" => "gameEnd",
        "description" => "End of game",
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )
);
END;
        $projectDirectory = $this->createTestProject($statesIncPhpContents);
        $project = new Project($projectDirectory->getFileInfo(), 'missing-game-setup');
        (new StateValidator())->validateStates($project);
    }

    public function testMissingGameEnd(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('stats.inc.php is missing gameEnd (99) state.');

        $statesIncPhpContents = <<<END
<?php
\$machinestates = array(
    1 => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array("" => 10)
    ),
    10 => array(
        "name" => "stuff",
        "description" => "",
        "type" => "game",
        "action" => "stStuff",
        "transitions" => array("" => 1)
    ),
    // Intentionally missing gameEnd
);
END;

        $projectDirectory = $this->createTestProject($statesIncPhpContents);
        $project = new Project($projectDirectory->getFileInfo(), 'missing-game-end');
        (new StateValidator())->validateStates($project);
    }

    /**
     * Verifies that an exception is thrown when transition defines an invalid/unknown state
     */
    public function testInvalidStateInTransition(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('State foo has transition to missing state ids 11');

        $statesIncPhpContents = <<<END
<?php
\$machinestates = array(
    1 => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array("" => 10)
    ),
    10 => array(
        "name" => "foo",
        "description" => "",
        "type" => "game",
        "action" => "stFoo",
        "transitions" => array("continue" => 11) // intentionally points to invalid state ID (11)
    ),
    99 => array(
        "name" => "gameEnd",
        "description" => "End of game",
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )
);
END;

        $projectDirectory = $this->createTestProject($statesIncPhpContents);
        $project = new Project($projectDirectory->getFileInfo(), 'looping');
        (new StateValidator())->validateStates($project);
    }

    /**
     * Verifies that an exception is thrown when gameSetup does not reach gameEnd
     */
    public function testGameSetupDoesNotReachGameEnd(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to get to gameEnd state from gameSetup.');

        $statesIncPhpContents = <<<END
<?php
\$machinestates = array(
    1 => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array("" => 10)
    ),
    10 => array(
        "name" => "foo",
        "description" => "",
        "type" => "game",
        "action" => "stFoo",
        "transitions" => array("continue" => 11)
    ),
    11 => array(
        "name" => "bar",
        "description" => "",
        "type" => "game",
        "action" => "stBar",
        "transitions" => array()
    ),
    99 => array(
        "name" => "gameEnd",
        "description" => "End of game",
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )
);
END;

        $projectDirectory = $this->createTestProject($statesIncPhpContents);
        $project = new Project($projectDirectory->getFileInfo(), 'looping');
        (new StateValidator())->validateStates($project);
    }

    /**
     * Verifies that the gameSetup to gameEnd validation works when there is a loop.
     */
    public function testStateLoop(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to get to gameEnd state from gameSetup.');

        $statesIncPhpContents = <<<END
<?php
\$machinestates = array(
    1 => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array("" => 10)
    ),
    10 => array(
        "name" => "foo",
        "description" => "",
        "type" => "game",
        "action" => "stFoo",
        "transitions" => array("continue" => 11)
    ),
    11 => array(
        "name" => "bar",
        "description" => "",
        "type" => "game",
        "action" => "stBar",
        "transitions" => array("continue" => 10) // intentionally goes back to 10
    ),
    99 => array(
        "name" => "gameEnd",
        "description" => "End of game",
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )
);
END;

        $projectDirectory = $this->createTestProject($statesIncPhpContents);
        $project = new Project($projectDirectory->getFileInfo(), 'looping');
        (new StateValidator())->validateStates($project);
    }

    private function createTestProject(string $statesIncPhpContents): WorkingDirectory
    {
        $workingDir = WorkingDirectory::createTemp();
        if (!file_put_contents(join(DIRECTORY_SEPARATOR, [$workingDir->getPathname(), 'states.inc.php']), $statesIncPhpContents)) {
            throw new \RuntimeException("Failed to write states.inc.php");
        }

        return $workingDir;
    }
}