<?php

namespace BGAWorkbench\Test;

class StubProductionEnvironment
{
    public static function stub()
    {
        // Stub for production PHP environment
        define('APP_GAMEMODULE_PATH', __DIR__ . '/../Stubs/');
        require_once(APP_GAMEMODULE_PATH . 'framework.php');
        require_once(APP_GAMEMODULE_PATH . 'APP_Object.inc.php');
        require_once(APP_GAMEMODULE_PATH . 'APP_DbObject.inc.php');
        require_once(APP_GAMEMODULE_PATH . 'APP_Action.inc.php');
        require_once(APP_GAMEMODULE_PATH . 'APP_GameAction.inc.php');
    }
}
