<?php

namespace BGAWorkbench\Test;

use BGAWorkbench\Project\WorkbenchProjectConfig;
use Faker\Factory;
use Faker\Generator;
use Functional as F;

class TableInstanceBuilder
{
    /**
     * @var WorkbenchProjectConfig
     */
    private $config;

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
     * @var Generator
     */
    private $faker;

    /**
     * @param WorkbenchProjectConfig $config
     */
    private function __construct(WorkbenchProjectConfig $config)
    {
        $this->config = $config;
        $this->options = [];
        $this->faker = Factory::create();
        $this->playerAmendments = [];
    }

    /**
     * @param array $players
     * @return self
     */
    public function setPlayers(array $players)
    {
        $this->players = F\map(
            $players,
            function (array $player, $i) {
                $defaultPlayer = $this->createDefaultPlayer($i);
                $allowedKeys = array_keys($defaultPlayer);
                $usedKeys = array_keys($player);
                $notAllowedKeys = array_diff($usedKeys, $allowedKeys);
                if (!empty($notAllowedKeys)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Invalid player keys: %s. Can only use %s',
                        join(', ', $notAllowedKeys),
                        join(', ', $allowedKeys)
                    ));
                }
                return array_merge($defaultPlayer, $player);
            }
        );
        return $this;
    }

    /**
     * @param array $playerAmendments
     * @return self
     */
    public function overridePlayersPostSetup(array $playerAmendments)
    {
        $this->playerAmendments = $playerAmendments;
        return $this;
    }

    /**
     * @param int $index
     * @return array
     */
    private function createDefaultPlayer($index)
    {
        return [
            'player_id' => $index + time(),
            'player_no' => $index + 1,
            'player_canal' => md5($index + time()),
            'player_name' => $this->faker->firstName,
            'player_avatar' => '000000'
        ];
    }

    /**
     * @param array $ids
     * @return self
     */
    public function setPlayersWithIds(array $ids)
    {
        return $this->setPlayers(array_map(function ($id) {
            return ['player_id' => $id];
        }, $ids));
    }

    /**
     * @param int $amount
     * @return self
     */
    public function setRandomPlayers($amount)
    {
        return $this->setPlayers(array_fill(0, $amount, []));
    }

    /**
     * @param array $options
     * @return self
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return TableInstance
     */
    public function build()
    {
        return new TableInstance($this->config, $this->players, $this->playerAmendments, $this->options);
    }

    /**
     * @param WorkbenchProjectConfig $config
     * @return TableInstanceBuilder
     */
    public static function create(WorkbenchProjectConfig $config)
    {
        return new self($config);
    }
}
