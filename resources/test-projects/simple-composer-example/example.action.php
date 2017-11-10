<?php

/**
 * @property Example game
 */
class action_example extends APP_GameAction
{
    public function __default()
    {
        if (self::isArg('notifwindow')) {
            $this->view = "common_notifwindow";
            $this->viewArgs['table'] = $this->getArg("table", AT_posint, true);
        } else {
            $this->view = "example_example";
            self::trace("Complete reinitialization of board game");
        }
    }
}
