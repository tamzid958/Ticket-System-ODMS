#!/bin/bash
php artisan migrate
php artisan shield:generate --all --panel panel
php artisan db:seed
exec "$@"