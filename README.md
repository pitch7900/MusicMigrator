# Music Migrator
Allow the migration of playlist between 
- iTunes Plist XML file
- Spotify
- Deezer

You need an account on Deezer API for developpers https://developers.deezer.com/ and create an app. https://developers.deezer.com/myapps

You'll also need an account on Spotify WebAPI developpeur page. See : https://developer.spotify.com/web-api
Following URL should be filled in the App declaration for Spotify :
 - http(s)://yoursiteurl:port/spotify/auth/sources
 - http(s)://yoursiteurl:port/spotify/auth/destinations
 - http(s)://yoursiteurl:port/spotify/me/about.json
 - http(s)://yoursiteurl:port
## 1. Configuration File needed
You'll need to create a /config/.env file with following parameters:
```ini
SITEURL="https://<Your site url>"
DEEZER_APIKEY="Deezer api key"
DEEZER_APISECRETKEY="Deezer secret key"
SPOTIFY_APIKEY="Spotify api key"
SPOTIFY_APISECRETKEY="Spotify secret key"
```
If "/config" directory and .env files are missing, then a configuration interface will appear (See chapter 2)

## 2. If the configuration file has not been created
A menu will pop up to help you fill the basic informations need to allow the app to run

## 3. Installation
Download the project from github,remove the vendor folder and reinstall composer packages 
```bash
git clone https://github.com/pitch7900/MusicMigrator.git
cd MusicMigrator
rm -rf vendor
composer install
```
and setup your webserver configuration to point to the /public folder.

For example, the project is downloaded to /var/www/MusicMigrator and the virtual host points to /var/www/MusicMigrator/public

```ApacheConf
<VirtualHost *:80>
        RewriteEngine On
        ServerName  <WEBSERVERNAME>
        ServerAlias <WEBSERVERNAME>
        Header set Access-Control-Allow-Origin "*"
        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/MusicMigrator/public

        ErrorLog ${APACHE_LOG_DIR}/MusicMigrator-error.log
        CustomLog ${APACHE_LOG_DIR}/MusicMigrator-access.log combined
        LogLevel alert rewrite:trace6

        <Directory "/var/www/MusicMigrator/public">
                Options Indexes FollowSymLinks
                AllowOverride All
        </Directory>
</VirtualHost>
```
## 4. Docker
For a build under docker see folder /Docker and run the startup.sh.

It will download the git project, recreate the vendor from composer and package everything for a docker image ready to run

## 5. Credits
- Throttler : https://github.com/hamburgscleanest/guzzle-advanced-throttle
- Deezer Wrapper : https://github.com/mbuonomo/Deezer-API-PHP-class/
- Seconds to HMS in twig : https://caffeinecreations.ca/blog/twig-macro-convert-seconds-to-hhmmss/
