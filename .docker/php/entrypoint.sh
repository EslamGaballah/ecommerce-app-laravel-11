#!/bin/bash

# Fix Laravel permissions for local development
chmod -R 777 /var/www/storage
chmod -R 777 /var/www/bootstrap/cache

exec "$@"
