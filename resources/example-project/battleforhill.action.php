<?php

/**
 * @property BattleForHill game
 */
class action_battleforhill extends APP_GameAction
{
    public function __default()
    {
        if (self::isArg('notifwindow')) {
            $this->view = "common_notifwindow";
            $this->viewArgs['table'] = $this->getArg("table", AT_posint, true);
        } else {
            $this->view = "battleforhill_battleforhill";
            self::trace("Complete reinitialization of board game");
        }
    }
}
