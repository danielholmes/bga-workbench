<?php

require_once(APP_BASE_PATH . "view/common/game.view.php");

/**
 * @property Example $game
 */
class view_example_example extends game_view
{
    public function getGameName()
    {
        return "example";
    }

    public function build_page($viewArgs)
    {

    }
}
