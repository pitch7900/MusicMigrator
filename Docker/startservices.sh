#!/bin/bash
SERVICENAME="musicmigrator"
echo "Init Script starting"


files=($(ls /docker-entrypoint-init.d/ -a))

for f in "${files[@]}"; do
    echo "Processing $f"
    case "$f" in
         .)        echo "Ignoring $f";;
         ..)       echo "Ignoring $f";;
         *.sh)     echo "$0: running $f"; . "/docker-entrypoint-init.d/$f" ;;
         *.tar.gz) echo "$0: running $f"; tar -zxf "/docker-entrypoint-init.d/$f" -C /var/www/ ; echo ;;
         *)        echo "$0: copying config file $f" ;cp -r "/docker-entrypoint-init.d/$f" /var/www/$SERVICENAME/config;;
    esac
    echo
done

echo "Creating /var/www/$SERVICENAME/log"; mkdir -p /var/www/$SERVICENAME/log ; echo ;
echo "Changing permissions in /var/www/$SERVICENAME"; chown -R www-data:www-data /var/www/$SERVICENAME ; echo ;


echo "Changing values in php.ini"
post_max_size=200M
upload_max_filesize=200M
max_execution_time=120
max_input_time=120
memory_limit=1024M


phpinipath=/etc/php/7.2/apache2/php.ini
for key in upload_max_filesize post_max_size max_execution_time max_input_time memory_limit
do
 echo "$key"
 sed -i "s/^\($key\).*/\1 $(eval echo = \${$key})/" $phpinipath
done

echo "Starting webserver"
exec /usr/sbin/apache2ctl -D FOREGROUND


