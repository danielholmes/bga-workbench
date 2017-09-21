#!/bin/bash

# Settings
PROJECT_NAME=$1
HOME_DIR=/home/ubuntu
PROJECT_DIR=$HOME_DIR/$PROJECT_NAME

# Make sure start in project directory
echo "cd $PROJECT_DIR" >> "$HOME_DIR/.bashrc"
echo "PATH=\$PATH:$PROJECT_DIR/vendor/bin" >> "$HOME_DIR/.bashrc"
# Not needed once separate out bga workbench project
echo "PATH=\$PATH:$PROJECT_DIR/bin" >> "$HOME_DIR/.bashrc"

# Install essential packages from Apt
apt-get update -y

# PHP and packages
apt-get install -y php7.0-cli php7.0-mbstring php7.0-dom php7.0-zip php7.0-mysql

# MySQL
debconf-set-selections <<< 'mysql-server mysql-server/root_password password bgawb'
debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password bgawb'
apt-get install -y mysql-server

# Composer
COMPOSER_INSTALLER="$PROJECT_DIR/etc/install_composer.sh"
chmod 755 $COMPOSER_INSTALLER
$COMPOSER_INSTALLER
mv composer.phar /usr/local/bin/composer

# Install composer deps
composer install -d $PROJECT_DIR
