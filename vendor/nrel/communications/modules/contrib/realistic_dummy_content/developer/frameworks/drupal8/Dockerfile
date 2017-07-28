FROM drupal:8.2

RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer
RUN composer global require drush/drush:8
RUN ln -s /root/.composer/vendor/drush/drush/drush /bin/drush

RUN apt-get update && apt-get upgrade -y
RUN apt-get install -y mysql-client

ADD ./frameworks/drupal8/docker-resources /resources

RUN drush dl devel -y

RUN cp -r sites/default /sites-default

EXPOSE 80
