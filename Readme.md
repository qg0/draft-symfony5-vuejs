# Installation
## Docker installation
Run the docker service
```
service docker start
```
Release the ```80```, ```3306``` and ```9000``` ports or change necessary ports in `.env` file 
```
service nginx stop; service mysql stop; service php-fpm stop; docker stop $(docker ps -a -q)
```
Create the container
```
docker-compose up
```
## Manual installation
### Edit the hosts file ```/etc/hosts```
```
127.0.0.1 mag.loc
```
Invalidate the ```/etc/hosts``` file
```
nscd -i hosts
```

### Nginx installation
#### Edit the virtual host config, e.g.: ```/etc/nginx/vhosts.d/mag.conf```
```
server {
    server_name mag.loc;
    root /srv/www/htdocs/mag.loc/public;

    location / {
        try_files $uri /index.php$is_args$args;
    }
      
    location ~.php$ {
        fastcgi_pass 127.0.0.1:65432;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_index index.php;
    }
}
```
#### Edit the nginx host in ```.env``` file
```
NGINX_SCHEME=http
NGINX_HOST=localhost
NGINX_PORT=80

PHPFPM_HOST=localhost
PHPFPM_PORT=9000

```

### Install the packages from the `composer.json` file
```
composer install
```
### Database creation

#### Add the database credentials to the `.env` file
```
DATABASE_DRIVER=mysql
DATABASE_USER=root
DATABASE_PASSWORD=password
DATABASE_HOST=localhost
DATABASE_PORT=3306
DATABASE_NAME=dbname
```

#### Create the database
```
bin/console doctrine:database:create 
```

#### Apply the migrations
```
bin/console doctrine:migrations:migrate
```

#### Apply the fixtures
```
bin/console doctrine:fixtures:load
```

### Frontend installation
#### Install npm packages
```
npm install
```

#### Apply security patches
```
npm audit fix
```

#### Production build
```
npm run build
```

#### Development runtime
```
npm run watch
```

# Quality control
## Coding standard
### Using the PHP Coding Standards Fixer
```
bin/php-cs-fixer fix src
bin/php-cs-fixer fix tests
```

### Using the PHP CodeSniffer
#### Install the Symfony coding standard
```
bin/phpcs --config-set installed_paths vendor/escapestudios/symfony2-coding-standard
```

#### Run the code sniffer
```
bin/phpcs --standard=Symfony src
bin/phpcs --standard=Symfony tests
```

## Testing
## Unit testing
```
bin/phpunit
```

## Unit testing in the docker container
```
docker exec -it php-fpm phpunit
```
