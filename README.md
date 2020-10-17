# Getting started

## Installation

Please check the official laravel installation guide for server requirements before you start. [Official Documentation](https://laravel.com/docs/7.x/installation#installation)

Clone the repository

    git clone git@github.com:syncedprojects/buy-event.git

Switch to the repo folder

    cd buy-event

Install all the dependencies using composer

    composer install

Generate a new application key

    php artisan key:generate

Run the database migrations (**Set the database connection in .env before migrating**)

    php artisan migrate

**TL;DR command list**

    git clone git@github.com:syncedprojects/buy-event.git
    cd buy-event
    composer install
    php artisan key:generate
    
**Make sure you set the correct database connection information before running the migrations** [Environment variables](#environment-variables)

    php artisan migrate

## Environment variables

- `.env` - Environment variables can be set in this file
