#!/bin/bash

source ~/.bashrc
composer install
npm install
cp .env.example .env
php artisan key:generate