#!/bin/sh
/usr/bin/php /data/www/artisan service:discovery && echo "注册ok" >>  /tmp/startup.log
/usr/bin/php /data/www/artisan migrate
su -s /bin/sh -c "nohup php artisan queue:work > /dev/null 2>&1 &" nginx
chmod -Rf 777 /data/www/storage
chmod -Rf 777 /data/www/bootstrap/cache