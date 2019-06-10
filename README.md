# PentaBee-api

A tracker for extra work.

### Contributors:

* Druta Mihai
* Staci Nicolae
* Stratan Ion
* Binzari Marin (mentor)


### Run docker

1. Have docker with docker compose installed
2. `docker-compose up -d`
3. Generate SSH private key: `docker-compose exec php-fpm /usr/bin/openssl genrsa -out /application/config/jwt/private.pem -aes256 4096`
4. Generate SSH public key: `docker-compose exec php-fpm /usr/bin/openssl rsa -pubout -in /application/config/jwt/private.pem -out /application/config/jwt/public.pem`
5. Copy `.env` to `.env.local` and modify accordingly
6. `docker-compose exec php-fpm composer install`
7. `docker-compose exec php-fpm bin/console doctrine:migrations:migrate -n`
8. `docker-compose exec php-fpm bin/console doctrine:fixtures:load -n`
9. Fix permissions: `docker-compose exec php-fpm /bin/chown -R www-data:www-data /application/config/jwt/private.pem /application/config/jwt/public.pem /application/var`
