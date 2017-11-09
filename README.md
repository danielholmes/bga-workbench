# BoardGameArena Workbench

[![Build Status](https://travis-ci.org/danielholmes/bga-workbench.svg?branch=master)](https://travis-ci.org/danielholmes/bga-workbench)

Set of tools to work with [BoardGameArena](https://boardgamearena.com/) projects. See
[The Battle for Hill 218](https://github.com/danielholmes/battle-for-hill-218) for an example usage.


## Usage

### Install

Via composer:

```
composer require --dev dhau/bga-workbench
```

### Initialise BGA Project

TODO: some command to generate a bgaproject file

### Deploying your project to BGA

```
bgawb build --deploy
```

### Continuous Deployment to Studio

Watches development files and deploys them as they change.

```
bgawb build --deploy --watch
```


## Development

### Requirements

 - [Vagrant](https://www.vagrantup.com/)


### Setting up Developer Machine

```
vagrant up
```


### Running Tests

```
vagrant ssh -c "composer test"
```


### Compilation Notes

See [https://github.com/mamuz/PhpDependencyAnalysis](https://github.com/mamuz/PhpDependencyAnalysis) if need a better
class dependency tree extraction.

`phpda analyze -- analysis.yml`

*analysis.yml*
```yaml
mode: 'usage'
source: './src/MyGame'
filePattern: '*.php'
formatter: 'PhpDA\Writer\Strategy\Json'
target: 'build/usage.json'
visitor:
  - PhpDA\Parser\Visitor\TagCollector
  - PhpDA\Parser\Visitor\SuperglobalCollector
```


### TODO

 - Better documentation
 - Output is pretty rough for build command and its variants (--deploy and --watch)
 - bga init command to interactively create project config + (optionally) deploy config
 - Attempt PHP 5.6 compat?
 - Lint files in watch before deploy
 - validate states file - states functions all present
 - better validate command - run all and show results at end rather than exit on first
 - When using the watch command - a changed file during the initial deploy won't redeploy
 - SFTP disconnects after a while - should be intelligent enough to reconnect
 - add bgawb to code sharing wiki doc
 - Tests!!!
 - find proper ext deps to put in composer (by trying on a fresh install and checking /etc/install.sh)
 - In future use https://github.com/krakjoe/pthreads to speed up deploys
 - find proper ext deps to put in composer (by trying on a fresh install)
 - ZombieTurnTestCase that runs one test per possible state
   - Need to generate tests dynamically - one method per state. Check phpunit hooks
   - check dataProvider - probably best to work the same as that
