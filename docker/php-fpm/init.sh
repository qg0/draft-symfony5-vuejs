#!/usr/bin/env bash
# PHP-FPM container initialisation

function progressbar() {
  echo -e "\n\n\033[1;34m ==================== $1% ==================== \033[0;32m\n\n"
}

php -d memory_limit=-1 /usr/bin/composer install -n

progressbar 10

while ! nc -z "${DATABASE_HOST}" "${DATABASE_PORT}" </dev/null; do sleep 5; done


bin/console doctrine:migrations:migrate -n

progressbar 20

bin/console doctrine:fixtures:load -n

progressbar 30

npm cache clean --force
rm ~/.npm -rf
rm node_modules -rf
rm package-lock.json -f
#npm rebuild node-sass &

progressbar 40

npm install

progressbar 50

npm install -g npm

progressbar 60

npm audit fix

progressbar 70

#npm run build

progressbar 80

npm run dev

progressbar 90

chmod +rw -R var 
bin/console cache:clear
bin/console cache:warmup

progressbar 100

echo -e "\n\n\033[1;34m Open your browser: http://localhost:${NGINX_PORT} \033[0;32m\n\n"
php-fpm
