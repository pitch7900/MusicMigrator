#!/bin/bash
#apt-get install -y composer git libapache2-mod-php php-mbstring
git clone https://github.com/pitch7900/MusicMigrator.git
rm -rf ./MusicMigrator/vendor
mv MusicMigrator musicmigrator
cd musicmigrator
composer install
cd ..
tar -czf musicmigrator.tar.gz musicmigrator
docker build .
echo "Start now the container with : docker-compose up" 
