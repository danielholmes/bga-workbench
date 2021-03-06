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

use BGAWorkbench\Console\Application;

$application = new Application();
$application->run();
