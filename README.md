# BoardGameArena Workbench

[![Build Status](https://travis-ci.org/danielholmes/bga-workbench.svg?branch=master)](https://travis-ci.org/danielholmes/bga-workbench)
[![Latest Stable Version](https://img.shields.io/packagist/v/dholmes/bga-workbench.svg)](https://packagist.org/packages/dholmes/bga-workbench)

A set of tools to work with [BoardGameArena](https://boardgamearena.com/) projects.

   * [Installation](#installation)
   * [Initialise BGA Project](#initialise-bga-project)
   * [Deploying to BGA Studio](#deploying-to-bga-studio)
   * [Compiling composer projects](#compiling-composer-projects)
   * [Testing utilities](#testing-utilities)

 
   * [Projects Using BGA Workbench](#projects-using-bga-workbench)

 
   * [Development](#development)
      * [Requirements](#requirements)
      * [Setting up Developer Machine](#setting-up-developer-machine)
      * [Running tests](#running-tests)


## Installation

Via composer:

```bash
composer require --dev dholmes/bga-workbench
```

To set up your project to work with BGA Workbench you need to have a `bgaproject.yml` file in the root. To generate one 
see the [`bgawb init` command](#initialise-bga-project).


## Initialise BGA Project

Once you've installed bgawb you can run the below command to interactively create a `bgaproject.yml` file in your 
current directory.

```bash
bgawb init
```


## Deploying to BGA Studio

```bash
bgawb build --deploy
```

### Continuous Deployment to Studio

Watches development files and deploys them as they change.

```bash
bgawb build --deploy --watch
```


## Compiling composer projects

The Board Game Arena production framework/environment doesn't natively support a [Composer](https://getcomposer.org/) 
project setup. By having `useComposer: true` set in your `bgaproject.yml` file, the 
[`bgawb build`](Deploying to BGA Studio) command will merge all non-dev composer dependencies inline into your .game.php 
file before deploying. 


## Testing Utilities

Some testing utilities are provided to help test various parts of a standard BGA project game.

### The Validate Command

Will run some basic checks on your project setup. e.g. whether you have the required files to function on the BGA 
platform (`.game.php`, `.action.php`, etc), whether your `states.inc.php` file is valid, etc. 

```bash
bgawb validate
```

### PHPUnit TestHelp trait

Including this trait and implementing the `createGameTableInstanceBuilder` method will set up and tear down a game table 
instance for each test that is run. Note that this makes use of the `setUp` and `tearDown` PHPUnit hooks

```php
<?php

namespace Game\Tests;

use PHPUnit\Framework\TestCase;
use BGAWorkbench\Test\TestHelp;
use Doctrine\DBAL\Connection;
use BGAWorkbench\Utils;

class ChooseAttackTest extends TestCase
{
    use TestHelp;
    
    protected function createGameTableInstanceBuilder() : TableInstanceBuilder
    {
        return $this->gameTableInstanceBuilder()
            ->setPlayersWithIds([66, 77])
            ->overridePlayersPostSetup([
                66 => ['player_color' => 'ff0000'],
                77 => ['player_color' => '00ff00']
            ]);
    }
    
    public function testAction()
    {
        $action = $this->table
            ->setupNewGame()
            ->withDbConnection(function (Connection $db) {
                $db->exec('INSERT battlefield_card (player_id, type, x, y) VALUES (' .
                    join('), (', [
                        [77, '"infantry"', 0, -1],  
                        [66, '"infantry"', 0, 1],  
                        [66, '"artillery"', 6, 1],  
                    ])
                . ')');
            })
            ->createActionInstanceForCurrentPlayer(66)
            ->stubActivePlayerId(66)
            ->stubArgs(['x' => 5, 'y' => 5]);

        $action->chooseAttack();
        
        // TODO: Run some asserts on the db
    }
    
    public function testStateFunc()
    {
        $game = $this->table
            ->setupNewGame()
            ->createGameInstanceWithNoBoundedPlayer()
            ->stubActivePlayerId(66);
        
        $game->stNextPlayer();
    }
    
    public function testGetAllDatas()
    {
        $game = $this->table
            ->setupNewGame()
            ->withDbConnection(function (Connection $db) {
                $db->exec('DELETE FROM deck_card');
                $db->exec('DELETE FROM playable_card');
                $db->exec('INSERT INTO battlefield_card (player_id, type, x, y) VALUES (66, "tank", 0, 2)');
                $db->executeUpdate('UPDATE player SET player_score_aux = 1 WHERE player_id = 66');
            })
            ->createGameInstanceForCurrentPlayer(66);

        $datas = Utils::callProtectedMethod($game, 'getAllDatas');
        
        // TODO: Some asserts on $datas
    }
}
```


## Projects Using BGA Workbench

 - [The Battle for Hill 218](https://github.com/danielholmes/battle-for-hill-218)
 - [Tablut](https://github.com/Lucas-C/tablut)


## Development

### Requirements

 - [Vagrant](https://www.vagrantup.com/)

### Setting up Developer Machine

```bash
vagrant up
```

### Running Tests

```bash
vagrant ssh
composer test
```
