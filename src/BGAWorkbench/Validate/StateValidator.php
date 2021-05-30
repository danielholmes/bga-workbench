<?php
namespace BGAWorkbench\Validate;

use BGAWorkbench\Project\Project;
use RuntimeException;
use Symfony\Component\Config\Definition\Processor;
use Functional as F;

/**
 * Validation of the states.inc.php file to make sure the state machine is complete and correct.
 *
 * Class StateValidator
 * @package BGAWorkbench\Validate
 */
class StateValidator
{
    const GAME_SETUP_ID = 1;
    const GAME_END_ID = 99;

    public function validateStates(Project $project): void
    {
        require_once(__DIR__ . '/../Stubs/framework.php');
        $variableName = 'machinestates';
        $fileName = 'states.inc.php';
        $states = $project->getFileVariableValue($fileName, $variableName)
            ->getOrThrow(new RuntimeException("Expect variable {$variableName} in {$fileName}"));

        $processor = new Processor();
        $validated = $processor->processConfiguration(new StateConfiguration(), [$states]);
        $stateIds = array_keys($states);

        // ensure all transitions reference valid states
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
                    throw new RuntimeException(
                        "State {$state['name']} has transition to missing state ids {$missingList} / ({$allList})"
                    );
                }
            }
        );

        $this->validateGameSetupState($states);
        $this->validateGameEndState($states);
        $this->validateGameSetupToEnd($states);
    }

    private function validateGameSetupState(array $states)
    {
        if (!array_key_exists(self::GAME_SETUP_ID, $states)) {
            throw new RuntimeException(
                "stats.inc.php is missing gameSetup (" . self::GAME_SETUP_ID . ") state.\nExample\n" . $this->getGameSetupStateStateExample()
            );
        }

        $gameSetupState = $states[self::GAME_SETUP_ID];
        $this->validatePropertiesAndValues($gameSetupState, 'gameSetup', [
            'name' => 'gameSetup',
            'description' => '',
            'type' => 'manager',
            'action' => 'stGameSetup',
        ], ['transitions'], $this->getGameSetupStateStateExample());

    }

    private function validateGameEndState(array $states)
    {
        if (!array_key_exists(self::GAME_END_ID, $states)) {
            throw new RuntimeException(
                "stats.inc.php is missing gameEnd (" . self::GAME_END_ID . ") state.\nExample\n" . $this->getGameEndStateStateExample()
            );
        }
        $gameSetupState = $states[self::GAME_END_ID];
        $this->validatePropertiesAndValues($gameSetupState, 'gameEnd', [
            'name' => 'gameEnd',
            'type' => 'manager',
            'action' => 'stGameEnd',
            'args' => 'argGameEnd'
        ], ['description'], $this->getGameEndStateStateExample());
    }

    /**
     * Verifies the state's properties and values.
     *
     * @param array $state
     * @param string $stateName
     * @param array $requiredPropertiesAndValues
     * @param array $additionalRequiredProperties
     * @param string $example
     */
    private function validatePropertiesAndValues(
        array $state,
        string $stateName,
        array $requiredPropertiesAndValues,
        array $additionalRequiredProperties,
        string $example
    ): void {
        $allRequiredProperties = array_merge($additionalRequiredProperties, array_keys($requiredPropertiesAndValues));
        $stateProperties =  array_keys($state);

        // make sure all of the properties exist
        $missing = array_diff($allRequiredProperties, $stateProperties);
        if (!empty($missing)) {
            throw new RuntimeException(
                "State {$stateName} missing required properties: " . join(", ", $missing) . ".\nExample\n{$example}"
            );
        }

        // make sure the values are the same
        foreach ($requiredPropertiesAndValues as $property => $expectedValue) {
            // The property should exist due to the check above
            if ($state[$property] !== $expectedValue) {
                throw new RuntimeException(
                    "State {$stateName} should have property {$property} equal to {$expectedValue}.\nActual: {$state[$property]}" . ".\nExample\n{$example}"
                );
            }
        }

        // make sure no additional properties are present
        $unexpected = array_diff($stateProperties, $allRequiredProperties);
        if (!empty($unexpected)) {
            throw new RuntimeException(
                "State {$stateName} has unexpected properties : " . join(", ", $unexpected) . ".\nExample\n{$example}"
            );
        }
    }

    /**
     * Verifies that the gameSetup state transitions to the gameEnd at some point.
     *
     * Assumes that transitions are all pointing to valid states and that gameSetup and gameEnd states exist.
     *
     * @param array $states
     */
    private function validateGameSetupToEnd(array $states): void
    {
        if (!array_key_exists(self::GAME_SETUP_ID, $states) || !array_key_exists(self::GAME_END_ID, $states)) {
            // Assumes that validate fails prior to this method when when missing gameSetup or gameEnd states
            return;
        }

        $visitedMap = [self::GAME_SETUP_ID => true];
        $reachesGameEnd = $this->visitState(self::GAME_SETUP_ID, $states[self::GAME_SETUP_ID], $states, $visitedMap);
        if (!$reachesGameEnd) {
            throw new RuntimeException("Unable to get to gameEnd state from gameSetup. Ensure that transitions have a path to the end.");
        }
    }

    /**
     * Using the specified state, depth first search through the transitions to find one that reaches gameEnd.
     * @param int $stateId The current state ID
     * @param array $state The current state info
     * @param array $states The map of all states where key is the state ID and value is state info.
     * @param array $visitedMap A map of all visited states. This is used to ensure that we don't get caught in a loop.
     * @return bool True if the gameEnd was reached.
     */
    private function visitState(int $stateId, array $state, array $states, array &$visitedMap)
    {
        if ($stateId === self::GAME_END_ID) {
            // we were able to get from start -> end without any issue
            return true;
        }

        foreach ($state['transitions'] as $nextStateId) {
            // Skip anything we've already visited. This happens when the states create a loop.
            if (isset($visitedMap[$nextStateId])) {
                continue;
            }

            $visitedMap[$nextStateId] = true;
            if (!array_key_exists($nextStateId, $states)) {
                // Assumes that the transitions have been verified prior to this method being called.
                continue;
            }

            $reachesGameEnd = $this->visitState($nextStateId, $states[$nextStateId], $states, $visitedMap);
            if ($reachesGameEnd === true) {
                return true;
            }
            unset($visitedMap[$nextStateId]);
        }

        // we've looked at every transition and didn't find an end
        return false;
    }

    private function getGameSetupStateStateExample(): string
    {
        return <<<END
1 => array(
    "name" => "gameSetup",
    "description" => "",
    "type" => "manager",
    "action" => "stGameSetup",
    "transitions" => []
)
END;
    }

    private function getGameEndStateStateExample(): string
    {
        return <<<END
1 => array(
    "name" => "gameEnd",
    "description" => clienttranslate("End of game"),
    "type" => "manager",
    "action" => "stGameEnd",
    "args" => "argGameEnd",
)
END;
    }
}