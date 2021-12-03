#INSTALLATION

##Install required packages
```bash
php composer install
php composer install-php-cs-fixer-in-tools-dir
```

##Setup database for development and test environments.
```bash
docker-compose up -d
php bin/console doctrine:migration:migrate
php bin/console --env=test doctrine:database:create
php bin/console --env=test doctrine:migration:migrate
```
##Setup keys and passphrase for LexikJWTAuthenticationBundle
```bash
php bin/console lexik:jwt:generate-keypair
```

##Run tests
```bash
php bin/phpunit
```