#!/bin/bash
set -e

# Download and install Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"

# Install PHP dependencies
php composer.phar install --no-dev --optimize-autoloader

# Install Node dependencies and build assets
npm install
npm run build