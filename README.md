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
composer require --dev dhau/bga-workbench
```

To set up your project to work with BGA Workbench you need to have a `bgaproject.yml` file in the root.
To generate one see the [`bgawb init` command](#initialise-bga-project).


## Initialise BGA Project

Once you've installed bgawb you can run the below command to interactively create a `bgaproject.yml`
file.

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

TODO


## Testing Utilities

TODO


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
