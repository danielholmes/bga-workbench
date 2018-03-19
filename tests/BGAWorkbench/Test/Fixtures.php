<?php

namespace BGAWorkbench\Test;

use BGAWorkbench\External\WorkbenchProjectConfigSerialiser;
use BGAWorkbench\Project\Project;
use BGAWorkbench\Project\WorkbenchProjectConfig;

class Fixtures
{
    /**
     * @param string $dirName
     * @return Project
     */
    public static function loadTestProject($dirName)
    {
        return self::loadTestProjectConfig($dirName)->loadProject();
    }

    /**
     * @param string $dirName
     * @return WorkbenchProjectConfig
     */
    public static function loadTestProjectConfig($dirName)
    {
        return WorkbenchProjectConfigSerialiser::readFromDirectory(
            new \SplFileInfo(__DIR__ . '/../../../resources/test-projects/' . $dirName)
        );
    }
}
