<?php

namespace BGAWorkbench\Test;

use BGAWorkbench\Project\Project;
use BGAWorkbench\Project\WorkbenchProjectConfig;
use BGAWorkbench\Utils;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Functional as F;

class TableInstance
{
    /**
     * @var WorkbenchProjectConfig
     */
    private $config;

    /**
     * @var Project
     */
    private $project;

    /**
     * @var array
     */
    private $players;

    /**
     * @var array
     */
    private $playerAmendments;

    /**
     * @var array
     */
    private $options;

    /**
     * @var DatabaseInstance
     */
    private $database;

    /**
     * @var boolean
     */
    private $isSetup;

    /**
     * @param WorkbenchProjectConfig $config
     * @param array $players
     * @param array $playerAmendments
     * @param array $options
     */
    public function __construct(WorkbenchProjectConfig $config, array $players, array $playerAmendments, array $options)
    {
        $this->config = $config;
        $this->project = $config->loadProject();
        $this->players = $players;
        $this->playerAmendments = $playerAmendments;
        $this->options = $options;
        $this->database = new DatabaseInstance(
            $config->getTestDbNamePrefix() . substr(md5(time()), 0, 10),
            $config->getTestDbUsername(),
            $config->getTestDbPassword(),
            [
                join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Stubs', 'dbmodel.sql']),
                $this->project->getDbModelSqlFile()->getPathname()
            ]
        );
        $this->isSetup = false;
    }

    /**
     * @return self
     */
    public function createDatabase()
    {
        $this->database->create();
        return $this;
    }

    /**
     * @return self
     */
    public function dropDatabaseAndDisconnect()
    {
        $this->database->drop();
        $this->database->disconnect();
        return $this;
    }

    /**
     * @param string $tableName
     * @param array $conditions
     * @return array
     */
    public function fetchDbRows($tableName, array $conditions = [])
    {
        return $this->database->fetchRows($tableName, $conditions);
    }

    /**
     * @param string $sql
     * @return mixed
     */
    public function fetchValue($sql)
    {
        return $this->database->fetchValue($sql);
    }

    /**
     * @return QueryBuilder
     */
    public function createDbQueryBuilder()
    {
        return $this->database->getOrCreateConnection()->createQueryBuilder();
    }

    /**
     * @return Connection
     */
    public function getDbConnection()
    {
        return $this->database->getOrCreateConnection();
    }

    /**
     * @return self
     */
    public function setupNewGame()
    {
        if ($this->isSetup) {
            throw new \RuntimeException('Already setup');
        }

        $this->isSetup = true;

        $game = $this->createGameInstanceWithNoBoundedPlayer();
        $gameClass = new \ReflectionClass($game);
        call_user_func([$gameClass->getName(), 'stubGameInfos'], $this->project->getGameInfos());
        call_user_func([$gameClass->getName(), 'setDbConnection'], $this->database->getOrCreateConnection());
        Utils::callProtectedMethod($game, 'setupNewGame', $this->createPlayersById(), $this->options);

        if (!empty($this->playerAmendments)) {
            foreach ($this->playerAmendments as $id => $player) {
                $numAffected = $this->getDbConnection()->update('player', $player, ['player_id' => $id]);
                if ($numAffected === 0) {
                    $found = (boolean) $this->getDbConnection()->executeQuery(
                        "SELECT COUNT(player_id) FROM player WHERE player_id = {$id}"
                    );
                    if (!$found) {
                        throw new \RuntimeException("No player with id {$id} found to override");
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param callable $callable
     * @return $this
     */
    public function withDbConnection($callable)
    {
        call_user_func($callable, $this->getDbConnection());
        return $this;
    }

    /**
     * @return \Table
     */
    public function createGameInstanceWithNoBoundedPlayer()
    {
        return $this->project->createGameTableInstance();
    }

    /**
     * @param int $currentPlayerId
     * @return \Table
     */
    public function createGameInstanceForCurrentPlayer($currentPlayerId)
    {
        $playerIds = array_map(function (array $player) {
            return $player['player_id'];
        }, $this->players);
        if (!in_array($currentPlayerId, $playerIds, true)) {
            $playerIdsList = join(', ', $playerIds);
            throw new \InvalidArgumentException("Current player {$currentPlayerId} not in {$playerIdsList}");
        }

        $game = $this->createGameInstanceWithNoBoundedPlayer();
        $game->stubCurrentPlayerId($currentPlayerId);
        return $game;
    }

    /**
     * @param int $currentPlayerId
     * @return \APP_GameAction
     */
    public function createActionInstanceForCurrentPlayer($currentPlayerId)
    {
        $action = $this->project->createActionInstance();
        $action->stubGame($this->createGameInstanceForCurrentPlayer($currentPlayerId));
        return $action;
    }

    /**
     * @param string $stateName
     * @param null|int $activePlayerId
     * @return \Table
     */
    public function runZombieTurn($stateName, $activePlayerId = null)
    {
        $state = F\first(
            $this->project->getStates(),
            function (array $state) use ($stateName) {
                return $state['name'] === $stateName;
            }
        );
        $game = $this->createGameInstanceWithNoBoundedPlayer();
        $game->zombieTurn($state, $activePlayerId);
        return $game;
    }

    /**
     * @return array
     */
    private function createPlayersById()
    {
        $ids = array_map(
            function ($i, array $player) {
                if (isset($player['player_id'])) {
                    return $player['player_id'];
                }
                return $i;
            },
            range(1, count($this->players)),
            $this->players
        );
        return array_combine($ids, $this->players);
    }
}
