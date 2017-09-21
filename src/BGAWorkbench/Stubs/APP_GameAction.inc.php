<?php

abstract class APP_GameAction extends APP_Action
{
    /**
     * @var Table
     */
    protected $game;

    /**
     * @param Table $game
     */
    public function stubGame(Table $game)
    {
        $this->game = $game;
    }

    /**
     * @return Table
     */
    public function getGame()
    {
        return $this->game;
    }

    /**
     * @param int $activePlayerId
     * @return self
     */
    public function stubActivePlayerId($activePlayerId)
    {
        $this->game->stubActivePlayerId($activePlayerId);
        return $this;
    }

    protected function ajaxResponse($dummy = '')
    {
        if ($dummy != '') {
            throw new InvalidArgumentException("Game action cannot return any data");
        }
    }
}
