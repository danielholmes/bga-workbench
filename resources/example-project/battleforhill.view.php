<?php

require_once(APP_BASE_PATH . "view/common/game.view.php");

/**
 * @property BattleForHill $game
 */
class view_battleforhill_battleforhill extends game_view
{
    public function getGameName()
    {
        return "battleforhill";
    }

    public function build_page($viewArgs)
    {

    }
}
