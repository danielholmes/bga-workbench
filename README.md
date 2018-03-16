# BoardGameArena Workbench

[![Build Status](https://travis-ci.org/danielholmes/bga-workbench.svg?branch=master)](https://travis-ci.org/danielholmes/bga-workbench)
[![Latest Stable Version](https://img.shields.io/packagist/v/dholmes/bga-workbench.svg)](https://packagist.org/packages/dholmes/bga-workbench)

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
bgawb build --deploy
```

### Continuous Deployment to Studio

Watches development files and deploys them as they change.

```
bgawb build --deploy --watch
```


## Projects Using BGA Workbench

 - [The Battle for Hill 218](https://github.com/danielholmes/battle-for-hill-218)
 - [Tablut](https://github.com/Lucas-C/tablut)


## Development

### Requirements

 - [Vagrant](https://www.vagrantup.com/)


### Setting up Developer Machine

```
vagrant up
```


### Running Tests

```
vagrant ssh
composer test
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
