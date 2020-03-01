FROM ubuntu:latest
LABEL version="1.0"
LABEL description="musicmigrator"
LABEL os="Ubuntu"

ENV TZ=Europe/Paris
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get -y update
RUN apt-get -y upgrade
RUN apt-get -y install \
               apache2 \
               libapache2-mod-php \
               php \
               php-curl \
               php-mbstring \
               php-xml
COPY apache.conf /etc/apache2/sites-available/000-default.conf
COPY Dockerfile /Dockerfile
RUN echo "ServerName MusicMigrator" >> /etc/apache2/apache2.conf
RUN a2enmod rewrite headers
EXPOSE 80/tcp
COPY startservices.sh /opt/startservices.sh
RUN chmod +x /opt/startservices.sh
WORKDIR /var/www/MusicMigrator
RUN mkdir -p /docker-entrypoint-init.d 
COPY musicmigrator.tar.gz /tmp/musicmigrator.tar.gz
RUN tar -zxvf /tmp/musicmigrator.tar.gz -C /var/www/
ENTRYPOINT ["/opt/startservices.sh"]



