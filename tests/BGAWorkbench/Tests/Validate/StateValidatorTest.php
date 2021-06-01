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
        "transitions" => array("repeat" => 10, "continue" => 12, "end" => 99)
    ),
    12 => array(
        "name" => "caz",
        "description" => "",
        "type" => "activeplayer",
        "args" => "argCaz",
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

        $projectDirectory = $this->createTestProject('valid', $statesIncPhpContents);
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
        $projectDirectory = $this->createTestProject('missinggamesetup', $statesIncPhpContents);
        $project = new Project($projectDirectory->getFileInfo(), 'missinggamesetup');
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

        $projectDirectory = $this->createTestProject('missinggameend', $statesIncPhpContents);
        $project = new Project($projectDirectory->getFileInfo(), 'missinggameend');
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

        $projectDirectory = $this->createTestProject('invalidstate', $statesIncPhpContents);
        $project = new Project($projectDirectory->getFileInfo(), 'invalidstate');
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

        $projectDirectory = $this->createTestProject('doesnotend', $statesIncPhpContents);
        $project = new Project($projectDirectory->getFileInfo(), 'doesnotend');
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

        $projectDirectory = $this->createTestProject('looping', $statesIncPhpContents);
        $project = new Project($projectDirectory->getFileInfo(), 'looping');
        (new StateValidator())->validateStates($project);
    }

    /**
     * Verifies that an exception is thrown when state defines an unknown function for "action" property.
     */
    public function testUnknownMethodInAction(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Action stUnknown defined in state 11 is not defined in Table.');

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
        "name" => "unknown",
        "description" => "",
        "type" => "game",
        "action" => "stUnknown",  // intentionally set to an unknown method
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

        $projectDirectory = $this->createTestProject('unknownaction', $statesIncPhpContents);
        $project = new Project($projectDirectory->getFileInfo(), 'unknownaction');
        (new StateValidator())->validateStates($project);
    }

    /**
     * Verifies that an exception is thrown when state defines an unknown function for "args" property.
     */
    public function testUnknownMethodInArgs(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Arguments argFoo defined in state 10 is not defined in Table.');

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
        "type" => "activeplayer",
        "args" => "argFoo",
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

        $projectDirectory = $this->createTestProject('unknownargs', $statesIncPhpContents);
        $project = new Project($projectDirectory->getFileInfo(), 'unknownargs');
        (new StateValidator())->validateStates($project);
    }

    private function createTestProject(string $projectName, string $statesIncPhpContents, ?string $gamePhpContents = null): WorkingDirectory
    {
        $workingDir = WorkingDirectory::createTemp();
        if (!file_put_contents(join(DIRECTORY_SEPARATOR, [$workingDir->getPathname(), 'states.inc.php']), $statesIncPhpContents)) {
            throw new RuntimeException("Failed to write states.inc.php");
        }

        if (is_null($gamePhpContents)) {
            $gamePhpContents = $this->createValidGamePhpContents($projectName);
        }
        if (!file_put_contents(join(DIRECTORY_SEPARATOR, [$workingDir->getPathname(), $projectName . '.game.php']), $gamePhpContents)) {
            throw new RuntimeException("Failed to write {$projectName}.game.php");
        }

        return $workingDir;
    }

    private function createValidGamePhpContents(string $projectName): string
    {
        return <<<END
<?php
require_once(APP_GAMEMODULE_PATH . 'module/table/table.game.php');

class {$projectName}Example extends Table
{
    protected function getGameName()
    {
        return $projectName;
    }
    protected function setupNewGame(\$players, \$options = [])
    {
    }   
    public function upgradeTableDb(\$from_version)
    {
    }
    
    public function stStuff()
    {
    }
    public function stFoo()
    {
    }
    public function stBar()
    {
    }
    public function argCaz()
    {
        return array();
    }
}
END;

    }
}
