<?php

$autoloadFiles = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php'
];
foreach ($autoloadFiles as $autoloadFile) {
    if (file_exists($autoloadFile)) {
        require_once($autoloadFile);
        break;
    }
}

use BGAWorkbench\Commands\BuildCommand;
use BGAWorkbench\Commands\ValidateCommand;
use BGAWorkbench\Commands\CleanCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new ValidateCommand());
$application->add(new BuildCommand());
$application->add(new CleanCommand());
$application->run();