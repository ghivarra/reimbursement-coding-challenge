#!/bin/bash

source ~/.bashrc
composer install
npm install
cp .env.example .env
php artisan migrate:install
php artisan migrate:fresh
php artisan db:seed DatabaseSeeder