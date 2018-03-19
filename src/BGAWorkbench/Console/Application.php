<?php

namespace BGAWorkbench\Console;

use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    /**
     * @param string $name
     * @param string $version
     */
    public function __construct(string $name = 'UNKNOWN', string $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);

        $this->add(new InitCommand());
        $this->add(new ValidateCommand());
        $this->add(new BuildCommand());
        $this->add(new CleanCommand());
    }
}
