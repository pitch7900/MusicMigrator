# iTunes2Deezer
Read an xml plist from iTunes and convert playlist (at your convenience) into a Deezer Playlist
You need an account on Deezer API for developpers https://developers.deezer.com/
and create an app
https://developers.deezer.com/myapps

## 1. Configuration File needed
You'll need to create a /config/.env file with following parameters:
```ini
SITEURL="https://<yoursiteurl>"
DZAPIKEY="<Deezer Application ID>"
DZAPI_SECRETKEY="<Deezer Secret Key>"
```
If "/conf" directory and .env files are missing, then a configuration interface will appear (See chapter 2)

## 2. If the configuration file has not been created
A menu will pop up to help you fill the basic informations need to allow the app to run

## 3. Installation
Download the project and setup your webserver configuration to point to the /public folder
For example, the project is downloaded to /var/www/iTunes2Deezer and the virtual host points to /var/www/iTunes2Deezer/public
```ApacheConf
<VirtualHost *:80>
        RewriteEngine On
        ServerName  <WEBSERVERNAME>
        ServerAlias <WEBSERVERNAME>
        Header set Access-Control-Allow-Origin "*"
        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/iTunes2Deezer/public

        ErrorLog ${APACHE_LOG_DIR}/itunes2deezer-error.log
        CustomLog ${APACHE_LOG_DIR}/itunes2deezer-access.log combined
        LogLevel alert rewrite:trace6

        <Directory "/var/www/iTunes2Deezer/public">
                Options Indexes FollowSymLinks
                AllowOverride All
        </Directory>
</VirtualHost>
```