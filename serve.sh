#!/bin/bash

sudo rm -rf /var/www/html && sudo cp -r -T ./frontend/client /var/www/html && sudo service apache2 restart