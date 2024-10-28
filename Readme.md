1. docker-compose up --build -d
2. composer install
3. docker-compose exec app php bin/console doctrine:migrations:migrate