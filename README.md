# BoardGameArena Workbench

[![Build Status](https://travis-ci.org/danielholmes/bga-workbench.svg?branch=master)](https://travis-ci.org/danielholmes/bga-workbench)

Set of tools to work with [BoardGameArena](https://boardgamearena.com/) projects. 


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
bgawb build -d
```

### Continuous Deployment to Studio

Watches development files and deploys them as they change.

```
bgawb build -d -w
```


## Development Requirements

 - [Vagrant](https://www.vagrantup.com/)


## Setting up Developer Machine

```
vagrant up
```


## Running Tests

```
vagrant ssh
composer test
```


## Compilation Notes

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


## TODO

 - When using the watch command - a changed file during the initial deploy won't redeploy
 - SFTP disconnects after a while - should be intelligent enough to reconnect
 - Output is pretty rough for build command and its variants (--deploy and --watch)
 - In future use https://github.com/krakjoe/pthreads to speed up deploys
 - work on separating BGAWorkbench
 - add bgawb to code sharing wiki doc
 - js linting/hint
 - options for re-implementations (sector 219 and other)
 - find proper ext deps to put in composer (by trying on a fresh install and checking /etc/install.sh)
