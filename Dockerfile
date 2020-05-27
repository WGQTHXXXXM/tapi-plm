FROM dockerhub.singulato.com/singulato/nginx-php:latest

WORKDIR /data/www

COPY . .

RUN cp ./etc/nginx/app.conf /etc/nginx/conf.d/app.conf && \
    crontab -u nginx /data/www/etc/crontab.conf && \
    chmod -Rf 777 ./storage && \
    chmod -Rf 777 ./bootstrap/cache

#RUN  composer install -vvv
#
#RUN php artisan route:cache && \
#    php artisan api:cache && \


RUN php artisan optimize --force && \
    composer dump-autoload

COPY scripts /scripts/pre-init.d