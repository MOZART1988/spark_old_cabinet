#!/bin/bash
cd /var/www/html/API
php artisan send:price $1 $2
