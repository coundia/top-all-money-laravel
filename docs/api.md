# swagger

php artisan install:api

composer require darkaonline/l5-swagger

[http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)


php artisan route:clear
php artisan config:clear
php artisan optimize:clear

php artisan l5-swagger:generate

php artisan route:list --path=documentation


php artisan storage:link
