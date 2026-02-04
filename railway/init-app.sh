#!/bin/bash

# Exit the script if any command fails
set -e

php artisan optimize:clear
php artisan migrate --force
php artisan storage:link
