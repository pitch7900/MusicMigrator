# iTunes2Deezer
Read a xml plist from iTunes and convert playlist (at your convenience) into a Deezer Playlist
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