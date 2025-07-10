#!/bin/bash

php artisan migrate:install
php artisan migrate:fresh
php artisan db:seed DatabaseSeeder